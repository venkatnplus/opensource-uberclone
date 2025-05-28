<?php

namespace App\Http\Controllers\Taxi\Web\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Taxi\Web\DriverSaveRequest;
use App\Http\Requests\Taxi\Web\DocumentUploadRequest;
// use App\Http\Requests\Taxi\Web\DumbCompanySaveRequest;
use App\Jobs\SendPushNotification;
use App\Models\boilerplate\OauthClients;
use App\Models\User;
use App\Models\taxi\Vehicle;
use App\Models\taxi\Driver;
use App\Models\taxi\DriverDocument;
use App\Models\boilerplate\Country;
use App\Models\taxi\Documents;
use App\Models\taxi\Wallet;
use App\Models\taxi\WalletTransaction;
use App\Models\taxi\Fine;
use App\Models\taxi\UserComplaint;
use App\Models\taxi\RequestRating;
use App\Models\taxi\Settings;
use App\Models\taxi\Submaster;
use App\Models\taxi\DriverSubscriptions;
use App\Models\taxi\DriverLogs;
use App\Models\taxi\DumpCompany;
use App\Models\taxi\VehicleModel;
use App\Models\taxi\RequestQuestions;
use App\Models\taxi\InvoiceQuestions;
use App\Models\taxi\Requests\Request as RequestModels;
use App\Models\taxi\ReferalAmountList;
use App\Traits\CommanFunctions;
use Illuminate\Support\Facades\Storage;
use App\Traits\RandomHelper;
use Illuminate\Support\Facades\Auth;
use App\Models\taxi\DocumentsGroup;
use App\Models\taxi\GoHome;
use App\Models\taxi\Zone;


use DB;
use Carbon\Carbon;
use App\Constants\PushEnum;
use DateTime;

class DriverSignupController extends Controller
{
    use CommanFunctions;
    use RandomHelper;
  
    public function driverAdd(Request $request)
    {
        
        $type = $request->type ? $request->type : 'edit';
        $types = Vehicle::where('status', 1)->get();
        $country = Country::where('status', 1)->get();
        $zone = Zone::where('status', 1)->get();
        $document = DocumentsGroup::where('status', 1)->get();
      
        return view('taxi.driver.WedAddDriver', [
            'types' => $types,
            'country' => $country,
            'zone'=> $zone,
            'type' => $type,
            'document' => $document,
        ]);
    }

    public function driverSave(DriverSaveRequest $request)
    {
        
        $driver = $request->all();

        $company = '';

        $userPh = User::where('phone_number', $request->phone_number)
            ->role('driver')
            ->first();

        if (!is_null($userPh)) {
            return response()->json(
                [
                    'status' => false,
                    'message' =>
                    'The phone number you provided is already associated with an existing account. Please try signing in or use a different phone number for registration  !!',
                ],
                403
            );
        }

        if ($request->has('company') && $driver['company']) {
            $company = User::where('slug', $driver['company'])
                ->role('Company')
                ->first();
            if ($company) {
                $drivers = Driver::where('company_id', $company->id)->count();
                if ($company->companyDetails->no_of_vehicle <= $drivers) {
                    return response()->json(
                        [
                            'message' =>
                            'Driver count completed in this company!...',
                            'status' => false,
                        ],
                        200
                    );
                }
            }
        }

        $user = User::create([
            'firstname' => $driver['first_name'],
            'lastname' => $driver['last_name'],
            'email' => $driver['email'],
            'phone_number' => $driver['phone_number'],
            'country' => $driver['country'],
            'country_code' => $driver['country'],
            'gender' => $driver['gender'],
            'address' => $driver['address']?? NULL,
            'active' => 0,
            'block_reson' => 'Admin Blocked',
        ]);

        if ($driver['company'] == '1') {
            $dumbcompany = DumpCompany::create([
                'company_name' => $driver['company_name'],
                'company_phone_number' => $driver['company_phone_number'],
                'status' => 1,
                'total_no_of_vehicle' => $driver['total_no_of_vehicle'],
                'user_id' => $user->id,
            ]);
        }

        if ($request->has('driver_image') && $driver['driver_image'] != '') {
            $filename = uploadImage(
                'images/profile',
                $request->file('driver_image')
            );

            // // AWS S3 Bucket Here
            // $filename = time().'.'.$request->driver_image->extension();
            // $path = Storage::disk('s3')->put('images/profile', $request->driver_image);
            // $paths = Storage::disk('s3')->put('', $request->driver_image);
            $user->profile_pic = $filename;
            $user->save();
        }

        $user->assignRole('driver');
        $client = new OauthClients();
        $client->user_id = $user->id;
        $client->name = $user->firstname;
        $client->secret = $this->generateRandomString(40);
        $client->redirect = 'http://localhost';
        $client->personal_access_client = false;
        $client->password_client = false;
        $client->revoked = false;
        $client->save();

        if ($request->has('vehicle_model')) {
            $fetchVechileModel = VehicleModel::where(
                'slug',
                $driver['vehicle_model']
            )->first();
            if ($fetchVechileModel) {
                $vechile_model = $fetchVechileModel->model_name;
            } else {
                $vechile_model = $driver['car_model'];
            }
        }

        $driverSave = Driver::create([
            'type' => $driver['type'],
            'car_number' => $driver['car_number'],
            'car_model' => $vechile_model,
            'car_year' => $driver['car_year'],
            'car_colour' => $driver['car_colour'],
            'city' => $driver['city'],
            'state' => $driver['state'],
            'pincode' => $driver['pincode'],
            'user_id' => $user->id,
            'company_id' => $company ? $company->id : null,
            'service_category' => $driver['service_type'],
            'service_location' => $driver['service_location'],
            'login_method' => $driver['category'],
            'notes' => $driver['notes']?? NULL,
            // 'approved_by' => auth()->user()->id,
            'status' => 1,
        ]);

        return response()->json(
            ['message' => 'success', 'status' => true,'data' => $user->slug ],
            200
        );
    }

    public function driverEdit(Request $request, $slug)
    {
        $type = $request->type ? $request->type : 'edit';
        $types = Vehicle::where('status', 1)->get();
        $country = Country::where('status', 1)->get();
        $zone = Zone::where('status', 1)->get();
        $company = User::role('Company')
            ->where('active', 1)
            ->get();
        $subscription = Submaster::get();

        $user = User::where('slug', $slug)->first();

        $models = VehicleModel::where('vehicle_id', $user->driver->type)->get();

        $model = VehicleModel::where('vehicle_id', $user->driver->type)
            ->where('model_name', $user->driver->car_model)
            ->first();
        if ($model) {
            $selected = '';
        } else {
            $selected = 'selected';
        }
        $user->driver->service_category = array_map(
            'trim',
            explode(',', $user->driver->service_category)
        );

        
        $documents = DocumentsGroup::where('status', 1)->get();
        $data_array = [];

        foreach ($documents as $key => $value) {
            foreach ($value->getDocument as $key1 => $value1) {
                $DriverDocuments = DriverDocument::where('user_id', $user->id)
                    ->where('document_id', $value1->id)
                    ->first();
                if ($DriverDocuments) {
                    $value->getDocument[$key1]->document_image =
                        $DriverDocuments->document_images;
                    $value->getDocument[$key1]->expiry_date =
                        $DriverDocuments->expiry_date;
                    $value->getDocument[$key1]->issue_date =
                        $DriverDocuments->issue_date;
                    $value->getDocument[$key1]->document_status =
                        $DriverDocuments->document_status;
                    $value->getDocument[$key1]->is_uploaded = 1;
                } else {
                    $value->getDocument[$key1]->document_image = '';
                    $value->getDocument[$key1]->expiry_dated = 0;
                    $value->getDocument[$key1]->issue_date = 0;
                    $value->getDocument[$key1]->is_uploaded = 0;
                }
            }

            $documents[$key]->get_document = $value->getDocument;
            if (count($value->getDocument) > 0) {
                array_push($data_array, $documents[$key]);
            }
        }
        $document = $data_array;
        if (!$user) {
            return redirect()->route('driver');
        }
        // // dd($data_array);
        return view('taxi.driver.WebDriverDocument', [
            'type' => $type,
            'types' => $types,
            'country' => $country,
            'user' => $user,
            'driver' => $user,
            'document' => $document,
            'subscription' => $subscription,
            'company' => $company,
            'models' => $models,
            'selected' => $selected,
            'zone' => $zone,

        ]);
    }

    public function driverUpdate(DriverSaveRequest $request)
    {
        $driver = $request->all();

        //  dd($driver);
        $userPh = User::where('phone_number', $request->phone_number)
            ->where('slug', $driver['slug'])
            ->role('driver')
            ->count();
        if ($userPh > 1) {
            return response()->json(
                [
                    'status' => false,
                    'message' =>
                    'User completed the registration process. So please login to continue !!',
                ],
                403
            );
        }

        $user = User::where('slug', $driver['slug'])->first();

        $company = '';
        if ($request->has('company') && $driver['company']) {
            $company = User::where('slug', $driver['company'])
                ->role('Company')
                ->first();
            if ($company) {
                $drivers = Driver::where('company_id', $company->id)->count();
                if ($company->companyDetails->no_of_vehicle <= $drivers) {
                    return response()->json(
                        [
                            'message' =>
                            'Driver count completed in this company!...',
                            'status' => false,
                        ],
                        200
                    );
                }
            }
        }

        $users = User::where('slug', $driver['slug'])->update([
            'firstname' => $driver['first_name'],
            'lastname' => $driver['last_name'],
            'email' => $driver['email'],
            'phone_number' => $driver['phone_number'],
            'country' => $driver['country'],
            'country_code' => $driver['country'],
            'gender' => $driver['gender'],
            'address' => $driver['address'],
        ]);
        if ($driver['company'] == '1') {
            $dummy_company = DumpCompany::where(
                'slug',
                $driver['company_slug']
            )->first();
            if ($dummy_company) {
                $dumbcompany = DumpCompany::where(
                    'slug',
                    $driver['company_slug']
                )->update([
                        'company_name' => $driver['company_name'],
                        'company_phone_number' => $driver['company_phone_number'],
                        'status' => 1,
                        'total_no_of_vehicle' => $driver['total_no_of_vehicle'],
                        'user_id' => $user->id,
                    ]);
            } else {
                $dumbcompany = DumpCompany::create([
                    'company_name' => $driver['company_name'],
                    'company_phone_number' => $driver['company_phone_number'],
                    'status' => 1,
                    'total_no_of_vehicle' => $driver['total_no_of_vehicle'],
                    'user_id' => $user->id,
                ]);
            }
        }

        if ($request->has('driver_image') && $driver['driver_image'] != '') {
            $filename = uploadImage(
                'images/profile',
                $request->file('driver_image')
            );

            // AWS S3 Bucket Here
            // $filename = time().'.'.$request->driver_image->extension();
            // $path = Storage::disk('s3')->put('images/profile', $request->driver_image);
            // $paths = Storage::disk('s3')->put('', $request->driver_image);
            $user->profile_pic = $filename;
            $user->save();
        }

        if ($request->has('vehicle_model')) {
            $fetchVechileModel = VehicleModel::where(
                'slug',
                $driver['vehicle_model']
            )->first();
            if ($fetchVechileModel) {
                $vechile_model = $fetchVechileModel->model_name;
            } else {
                $vechile_model = $driver['car_model'];
            }
        }

        $drivers = Driver::where('user_id', $user->id)->first();
        // dd($drivers);
        if (!$drivers) {
            $driverSave = Driver::create([
                'type' => $driver['type'],
                'car_number' => $driver['car_number'],
                'car_model' => $vechile_model,
                'car_year' => $driver['car_year'],
                'car_colour' => $driver['car_colour'],
                'user_id' => $user->id,
                'city' => $driver['city'],
                'state' => $driver['state'],
                'pincode' => $driver['pincode'],
                'company_id' => $company ? $company->id : null,
                'service_category' => $driver['service_type'],
                'service_location' => $driver['service_location'],
                'login_method' => $driver['category'],
                'notes' => $driver['notes'],
                // 'approved_by' => auth()->user()->id,
                'status' => 1,
            ]);
        } else {
            $driverSave = Driver::where('user_id', $user->id)->update([
                'type' => $driver['type'],
                'car_number' => $driver['car_number'],
                'car_model' => $vechile_model,
                'car_year' => $driver['car_year'],
                'car_colour' => $driver['car_colour'],
                'city' => $driver['city'],
                'state' => $driver['state'],
                'company_id' => $company ? $company->id : null,
                'pincode' => $driver['pincode'],
                'service_category' => $driver['service_type'],
                'service_location' => $driver['service_location'],
                'login_method' => $driver['category'],
                // 'notes' => $driver['notes'],
            ]);
        }

        if ($request->has('subscription') && $driver['subscription'] != '') {
            $old_subscription = DriverSubscriptions::where('user_id', $user->id)
                ->where('to_date', '>=', NOW())
                ->count();

            if ($old_subscription > 0) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Sorry!, You are already subscriped.',
                    ],
                    200
                );
            }

            $subscription = Submaster::where(
                'slug',
                $driver['subscription']
            )->first();

            $wallet = Wallet::where('user_id', $user->id)->first();

            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'earned_amount' => 0,
                    'balance_amount' => 0,
                    'amount_spent' => 0,
                ]);
            }

            if ($subscription->amount > $wallet->balance_amount) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Sorry! low balaance in wallet.',
                    ],
                    200
                );
            }

            $driver_subcription = DriverSubscriptions::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'from_date' => NOW(),
                'to_date' => Carbon::now()->addDays($subscription->validity),
                'amount' => $subscription->amount,
                'paid_status' => 1,
            ]);

            $wallet->balance_amount -= $subscription->amount;
            $wallet->earned_amount += $subscription->amount;
            $wallet->save();

            $wallet_transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $subscription->amount,
                'purpose' => 'wallet spant amount to subscription',
                'type' => 'SPENT',
                'user_id' => $user->id,
            ]);
        }

        return response()->json(
            ['status' => true, 'message' => 'success'],
            200
        );
    }

    public function driverDocument($slug)
    {
        $user = User::where('slug', $slug)->first();
        $document = Documents::where('status', 1)->get();

        foreach ($document as $key => $value) {
            $driverDocument = DriverDocument::where('user_id', $user->id)
                ->where('document_id', $value->id)
                ->first();
            $document[$key]->date_required = $document[$key]->expiry_date;
            if ($driverDocument) {
                $document[$key]->document_image =
                    $driverDocument->document_images;
                $document[$key]->expiry_date = $driverDocument->expiry_date;
                $document[$key]->issue_date = $driverDocument->issue_date;
                $document[$key]->document_status =
                    $driverDocument->document_status;
                $document[$key]->is_uploaded = 1;
            } else {
                $document[$key]->document_image = '';
                $document[$key]->expiry_dated = '';
                $document[$key]->issue_dated = '';
                $document[$key]->document_status = '';
                $document[$key]->is_uploaded = 0;
            }
        }
        // dd($document[$key]->expiry_dated);
        //   if($document[$key]->expiry_dated <= Carbon::now())
        return view('taxi.driver.DriverDocumentList', [
            'user' => $user,
            'document' => $document,
        ]);
    }

    public function driverDocumentEdit($user, $slug)
    {
        $driver_document = Documents::where('slug', $slug)->first();
        $users = User::where('slug', $user)->first();
        $driverDocument = DriverDocument::where('user_id', $users->id)
            ->where('document_id', $driver_document->id)
            ->first();
        if ($driverDocument) {
            $driver_document->document_image = $driverDocument->document_images;
            $driver_document->expiry_dated = $driverDocument->expiry_date;
            $driver_document->issue_dated = $driverDocument->issue_date;
            $driver_document->is_uploaded = 1;
            $driver_document->identifier_no = $driverDocument->identifier;
            // dd($driver_document->document_image);
        } else {
            $driver_document->document_image = '';
            $driver_document->expiry_dated = '';
            $driver_document->issue_dated = '';
            $driver_document->is_uploaded = 0;
            $driver_document->identifier_no = '';
        }
        return response()->json(
            ['message' => 'success', 'data' => $driver_document],
            200
        );
    }

    public function driverDocumentUpdate(DocumentUploadRequest $request)
    {
        $data = $request->all();
        if ($data['expiry_date'] != "" && $data['expiry_date'] <= date('Y-m-d')) {
            return response()->json(['message' => 'date expried', 'success' => false], 200);
        }

        $users = User::where('slug', $data['driver_id'])->first();
        $document = Documents::where('slug', $data['document_id'])->first();

        $driverDocument = DriverDocument::where('user_id', $users->id)
            ->where('document_id', $document->id)
            ->first();

        $filename = uploadImage(
            'images/document',
            $request->file('document_image')
        );

        // $filename = time().'.'.$request->document_image->extension();
        // $path = Storage::disk('s3')->put('images/document', $request->document_image);
        // $paths = Storage::disk('s3')->put('', $request->document_image);

        if ($driverDocument) {
            // Storage::disk('s3')->delete('images/document/' . $filename);
            deleteImage('images/document', $driverDocument->document_image);
            $driverDocument->document_image = $filename;
            $driverDocument->document_status = 1;
            $driverDocument->expiry_date =
                $document->expiry_date == 1 ? $data['expiry_date'] : null;
            $driverDocument->issue_date =
                $document->expiry_date == 2 ? $data['expiry_date'] : null;
            $driverDocument->exprienc_status = 0;
            $driverDocument->exprience_reson = '';
            $driverDocument->identifier =$data['identifier'] ? $data['identifier'] : null;
            $driverDocument->save();
        } else {
            if(!is_null($data['identifier'])){
                $driverDocument = DriverDocument::where('identifier', $data['identifier'])
                ->where('document_id', '3')
                ->first();
                if(!is_null($driverDocument)){
                    return response()->json(['message' => 'The Aadhar number you provided is already associated with an existing account', 'success' => false], 200);
                }
            }
           
            DriverDocument::create([
                'user_id' => $users->id,
                'document_id' => $document->id,
                'document_image' => $filename,
                'expiry_date' =>
                $document->expiry_date == 1 ? $data['expiry_date'] : null,
                'issue_date' =>
                $document->expiry_date == 2 ? $data['expiry_date'] : null,
                'identifier' => $document->identifier == 1 ?  $data['identifier'] : null,
                'document_status' => 1,
                'status' => 1,
                'exprienc_status' => 0,
                'exprience_reson' => '',
            ]);
        }

        $documents = Documents::where('requried', 1)->where('status',1)
            ->pluck('id')
            ->toArray();
        $driver_documents = DriverDocument::where('user_id', $users->id)
            ->whereIn('document_id', $documents)
            ->count();
        if (count($documents) <= $driver_documents) {
            Driver::where('user_id', $users->id)->update([
                'document_upload_status' => 4,
            ]);
        }

        return response()->json(['message' => 'success', 'success' => true], 200);
    }

    public function driverDocumentApproved(Request $request)
    {
        $data = $request->all();
        $users = User::where('slug', $data['driver_id'])->first();


        $documents = Documents::where('requried', 1)
            ->where('status', 1)
            ->pluck('id')
            ->toArray();

        $myDocuments = DriverDocument::where('user_id', $users->id)
            ->where('document_status', 2)
            ->whereIn('document_id', $documents)
            ->count();
        $myDeniteDocuments = DriverDocument::where('user_id', $users->id)
            ->where('document_status', 0)
            ->count();
        $driver_documents = DriverDocument::where('user_id', $users->id)
            ->whereIn('document_id', $documents)
            ->count();

        if (count($documents) <= $myDocuments) {
            session()->flash(
                'message',
                'Driver Document  uploaded and successfully!...'
            );
            // return redirect()->route('driverDocumentEdit');
           
            return response()->json(
                [
                    'message' =>
                    'Driver Document  uploaded and successfully!...',
                ],
                200
            );

        } else {
            $documents = Documents::where('requried', 1)
                ->where('status', 1)
                ->pluck('id')
                ->toArray();
            $myDocuments = DriverDocument::where('user_id', $users->id)
                ->whereIn('document_id', $documents)
                ->get();
            // dd($myDocuments);
            if (count($documents) > $myDocuments->count()) {
                session()->flash(
                    'message',
                    'Driver Document not uploaded !...'
                );
                // return redirect()->route('driverDocumentEdit');
               
                return response()->json(
                    [
                        'message' =>
                        'Driver Document not uploaded !...',
                    ],
                    200
                );
            }

        }
        return response()->json(['message' => 'success'], 200);
    }
    public function DriverGetModel($slug)
    {
        $models = VehicleModel::where('vehicle_id', $slug)->get();
        return response()->json(
            ['message' => 'success', 'models' => $models],
            200
        );
    }
}
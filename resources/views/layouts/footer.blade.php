<!-- Footer -->
<div class="navbar navbar-expand-lg navbar-light">
				<div class="text-center d-lg-none w-100">
					<button type="button" class="navbar-toggler dropdown-toggle" data-toggle="collapse" data-target="#navbar-footer">
						<i class="icon-unfold mr-2"></i>
						Footer
					</button>
				</div>

				<div class="navbar-collapse collapse" id="navbar-footer">
					<span class="navbar-text">
					&copy; <?php echo date("Y");?>&nbsp;{{ settingValue('application_name') ? settingValue('application_name') : config('app.name', 'Taxi Appz') }}.
					</span>
				</div>
			</div>
			<!-- /footer -->
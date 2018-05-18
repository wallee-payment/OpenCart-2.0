<?php echo $header ?>
<?php echo $column_left ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">

			<div class="pull-right">
				<button type="submit" data-toggle="tooltip" form="form-wallee"
					title="<?php echo htmlspecialchars($button_save) ?>"
					class="btn btn-primary">
					<i class="fa fa-save"></i>
				</button>
				<a href="<?php echo $cancel ?>" data-toggle="tooltip"
					title="<?php echo htmlspecialchars($button_cancel) ?>"
					class="btn btn-default"><i class="fa fa-reply"></i></a>
			</div>
			<h1><?php echo htmlspecialchars($heading_title) ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
				<li><a href="<?php echo $breadcrumb['href'] ?>"><?php echo $breadcrumb['text'] ?></a></li>
				<?php } ?>
			</ul>

		</div>
	</div>
	<div class="container-fluid">

		<form action="<?php echo $action ?>" method="post"
			enctype="multipart/form-data" id="form-wallee"
			class="form-horizontal">
			
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">
						<i class="fa fa-pencil"></i> <?php echo htmlspecialchars($text_edit) ?></h3>
				</div>
				<div class="panel-body">
					<fieldset>
						<legend><?php echo htmlspecialchars($title_global_settings) ?></legend>
						<div class="form-group required">
							<label class="col-sm-2 control-label" for="wallee_user_id"><span
								data-toggle="tooltip"
								title="<?php echo htmlspecialchars($help_user_id) ?>"><?php echo htmlspecialchars($entry_user_id) ?></span></label>

							<div class="col-sm-10">
								<input type="text" name="wallee_user_id"
									value="<?php echo htmlspecialchars($wallee_user_id) ?>"
									id="wallee_user_id" class="form-control" />
							</div>
						</div>

						<div class="form-group required">
							<label class="col-sm-2 control-label"
								for="wallee_application_key"><span data-toggle="tooltip"
								title="<?php echo htmlspecialchars($help_application_key) ?>"><?php echo htmlspecialchars($entry_application_key) ?></span></label>

							<div class="col-sm-10">
								<input type="password" name="wallee_application_key"
									value="<?php echo htmlspecialchars($wallee_application_key) ?>"
									id="wallee_application_key" class="form-control" />
							</div>
						</div>
					</fieldset>
				</div>
			</div>
	
			<ul class="nav nav-tabs">
			  <?php foreach($shops as $store) { ?> 
			  	<li class="<?php if ($store['id'] == 0) { echo "active"; } ?>"><a
					data-toggle="tab" href="#store<?php echo $store['id']; ?>"><?php echo $store['name']; ?></a></li>
			  <?php } ?>
			</ul>

			<div class="tab-content">
		
		  <?php foreach ($shops as $store) { ?>
			  <div id="store<?php echo $store['id']; ?>"
					class="tab-pane fade in <?php if($store['id'] == 0) { echo "active"; } ?>">

				<?php if ($error_warning) { ?>
				<div class="alert alert-danger">
						<i class="fa fa-exclamation-circle"></i>
					<?php echo htmlspecialchars($error_warning) ?>
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					</div>
				<?php } ?>
				<?php if ($success): ?>
				<div class="alert alert-success">
						<i class="fa fa-exclamation-circle"></i>
					<?php echo htmlspecialchars($success) ?>
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					</div>
				<?php endif; ?>
				
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">
								<i class="fa fa-pencil"></i> <?php echo htmlspecialchars($text_edit) ?></h3>
						</div>
						<div class="panel-body">
							<fieldset>
								<legend><?php echo htmlspecialchars($title_store_settings) ?></legend>

								<div class="form-group required">
									<label class="col-sm-2 control-label" for="stores[<?php echo $store['id']; ?>][wallee_status]"><?php echo $entry_status; ?></label>

									<div class="col-sm-10">
										<select	name="stores[<?php echo $store['id']; ?>][wallee_status]"
											id="stores[<?php echo $store['id']; ?>][wallee_status]" class="form-control">
											<option value="0"<?php if (!$stores[$store['id']]['wallee_status']) { echo 'selected="selected"'; }?>><?php echo $text_disabled;?></option>
											<option value="1"<?php if ($stores[$store['id']]['wallee_status']) { echo 'selected="selected"'; }?>><?php echo $text_enabled;?></option>
										</select>
									</div>
								</div>

								<div class="form-group required">
									<label class="col-sm-2 control-label" for="stores[<?php echo $store['id']; ?>][wallee_space_id]"><span
										data-toggle="tooltip"
										title="<?php echo htmlspecialchars($help_space_id) ?>"><?php echo htmlspecialchars($entry_space_id) ?></span></label>

									<div class="col-sm-10">
										<input type="text"
											name="stores[<?php echo $store['id']; ?>][wallee_space_id]"
											value="<?php echo htmlspecialchars($stores[$store['id']]['wallee_space_id']) ?>"
											id="stores[<?php echo $store['id']; ?>][wallee_space_id]" class="form-control" />
									</div>
								</div>

								<div class="form-group">
									<label class="col-sm-2 control-label"
										for="stores[<?php echo $store['id']; ?>][wallee_space_view_id]"><span data-toggle="tooltip"
										title="<?php echo htmlspecialchars($help_space_view_id) ?>"><?php echo htmlspecialchars($entry_space_view_id) ?></span></label>

									<div class="col-sm-10">
										<input type="text"
											name="stores[<?php echo $store['id']; ?>][wallee_space_view_id]"
											value="<?php echo htmlspecialchars($stores[$store['id']]['wallee_space_view_id']) ?>"
											id="stores[<?php echo $store['id']; ?>][wallee_space_view_id]" class="form-control" />
									</div>
								</div>
							</fieldset>

							<fieldset>
								<legend><?php echo htmlspecialchars($title_downloads) ?></legend>

								<div class="form-group">
									<label class="col-sm-2 control-label"><?php echo htmlspecialchars($entry_download_invoice) ?></label>

									<div class="col-sm-10">
										<input type="checkbox"
											name="stores[<?php echo $store['id'] ?>][wallee_download_invoice]"
											<?php if($stores[$store['id']]['wallee_download_invoice']) { ?>
											checked <?php } ?> value="1"/>
										<p class="form-control-static"><?php echo $description_download_invoice ?></p>
									</div>
								</div>

								<div class="form-group">
									<label class="col-sm-2 control-label"><?php echo htmlspecialchars($entry_download_packaging) ?></label>

									<div class="col-sm-10">
										<input type="checkbox"
											name="stores[<?php echo $store['id'] ?>][wallee_download_packaging]"
											<?php if($stores[$store['id']]['wallee_download_packaging']) { ?>
											checked <?php } ?> value="1"/>
										<p class="form-control-static"><?php echo $description_download_packaging ?></p>
									</div>
								</div>
							</fieldset>

							<fieldset>
								<legend><?php echo htmlspecialchars($title_payment_status) ?></legend>
								
								<?php foreach ($wallee_statuses as $status): ?>
								
								<div class="form-group">
									<label class="col-sm-2 control-label"
										for="stores[<?php echo $store['id']; ?>][<?php echo $status['key'];?>]"> <span
										data-toggle="tooltip"
										title="<?php echo $status['description']; ?>">
									<?php echo htmlspecialchars($status['entry']) ?></span></label>

									<div class="col-sm-10">
										<select
											name="stores[<?php echo $store['id']; ?>][<?php echo $status['key'];?>]"
											id="stores[<?php echo $store['id']; ?>][]<?php echo $status['key'];?>]" class="form-control">
											<?php foreach ($order_statuses as $order_status): ?>
											<?php if ($order_status['order_status_id'] == $stores[$store['id']][$status['key']]): ?>
											<option
												value="<?php echo $order_status['order_status_id'] ?>"
												selected="selected"><?php echo $order_status['name'] ?></option>
											<?php else: ?>
											<option
												value="<?php echo $order_status['order_status_id'] ?>"><?php echo $order_status['name'] ?></option>
											<?php endif ?>
											<?php endforeach ?>
										</select>
									</div>
								</div>

								<?php endforeach; ?>
							</fieldset>
							
							<fieldset>
								<legend><?php echo htmlspecialchars($title_debug) ?></legend>
								
								<div class="form-group">
									<label class="col-sm-2 control-label"
										for="stores[<?php echo $store['id']; ?>][wallee_log_level]"><span data-toggle="tooltip"
										title="<?php echo htmlspecialchars($help_log_level) ?>"><?php echo htmlspecialchars($entry_log_level) ?></span></label>

									<div class="col-sm-10">
										<select class="form-control" name="stores[<?php echo $store['id']; ?>][wallee_log_level]" id="stores[<?php echo $store['id']; ?>][wallee_log_level]">
										<?php foreach($log_levels as $level => $name) :?>
											<option value="<?php echo $level;?>" 
											<?php if ($level == $stores[$store['id']]['wallee_log_level']): ?>
											 selected="select"
											<?php endif; ?>
											><?php echo $name; ?></option>
										<?php endforeach; ?>
										</select>
									</div>
								</div>
							</fieldset>
						</div>
					</div>
				</div>
			<?php } ?>
				</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">
						<i class="fa fa-pencil"></i> <?php echo $text_information; ?></h3>
				</div>
				<div class="panel-body">
					<fieldset>
						<legend><?php echo $title_modifications; ?></legend>
	
						<div class="form-group">
							<label class="col-sm-2 control-label"><?php echo $entry_core; ?></label>
	
							<div class="col-sm-10">
								<p class="form-control-static"><?php echo $description_core; ?></p>
							</div>
						</div>
	
						<div class="form-group">
							<label class="col-sm-2 control-label"><?php echo $entry_administration; ?></label>
	
							<div class="col-sm-10">
								<p class="form-control-static"><?php echo $description_administration; ?></p>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-sm-2 control-label"><?php echo $entry_email; ?></label>
	
							<div class="col-sm-10">
								<p class="form-control-static"><?php echo $description_email; ?></p>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-sm-2 control-label"><?php echo $entry_alerts; ?></label>
	
							<div class="col-sm-10">
								<p class="form-control-static"><?php echo $description_alerts; ?></p>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-sm-2 control-label"><?php echo $entry_pdf; ?></label>
	
							<div class="col-sm-10">
								<p class="form-control-static"><?php echo $description_pdf; ?></p>
							</div>
						</div>
	
						<div class="form-group">
							<label class="col-sm-2 control-label"><?php echo $entry_checkout; ?></label>
	
							<div class="col-sm-10">
								<p class="form-control-static"><?php echo $description_checkout; ?></p>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-sm-2 control-label"><?php echo $entry_events; ?></label>
	
							<div class="col-sm-10">
								<p class="form-control-static"><?php echo $description_events; ?></p>
							</div>
						</div>
					</fieldset>
	
					<fieldset>
						<legend><?php echo $title_migration ?></legend>
	
						<div class="form-group">
							<label class="col-sm-2 control-label"><?php echo $entry_migration_name ?></label>
							<div class="col-sm-10">
								<p class="form-control-static"><?php echo $wallee_migration_name ?></p>
							</div>
						</div>
	
						<div class="form-group">
							<label class="col-sm-2 control-label"><?php echo $entry_migration_version ?></label>
							<div class="col-sm-10">
								<p class="form-control-static"><?php echo $wallee_migration_version ?></p>
							</div>
						</div>
					</fieldset>
				</div>
			</div>
		</form>
	</div>
</div>
<?php echo $footer ?>

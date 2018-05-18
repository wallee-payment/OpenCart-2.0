<!-- see model/extension/wallee/order.php & WalleeAdministration.ocmod.xml -->
<?php foreach($job_groups as $key => $job_group) : ?>
	<a class='h3' data-toggle='collapse' href='#wallee-jobs-<?php echo $key;?>' aria-controls='wallee-jobs-<?php echo $key;?>' aria-expanded='<?php echo $key == 0 ? 'true' : 'false'?>'
	><?php echo $job_group['title']; ?> <i class='fa fa-chevron-down'></i></a>
	
	<div id='wallee-jobs-<?php echo $key; ?>' class='collapse'>
	<?php foreach ($job_group['jobs'] as $job) : ?>
		<hr/>
		<?php if(isset($job['title'])): ?>
		<h4><?php echo $job['title']; ?></h4>
		<?php endif; ?>
	
		<dl class='row'>
		<?php foreach($job['label_groups'] as $label_group) : ?>
			<dt class='col-sm-3'>
				<strong data-toggle='tooltip' data-placement='top' title='<?php echo $label_group['description']?>'>
				<?php echo $label_group['name']; ?>
				</strong>
			</dt>
			<dd class='col-sm-9'>
			<?php foreach($label_group['labels'] as $label) : ?>
				<dl class='row'>
					<dt class='col-sm-3'>
						<strong data-toggle='tooltip' data-placement='top' title='<?php echo $label['description']?>'>
						<?php echo $label['name']; ?>
						</strong>
					</dt>
					<dd class='col-sm-9'>
					<?php echo $label['value']; ?>
					</dd>
				</dl>
			<?php endforeach; ?>
			</dd>
		<?php endforeach; ?>
		</dl>
	<?php endforeach; ?>
	</div>
	<hr/>
<?php endforeach; ?>
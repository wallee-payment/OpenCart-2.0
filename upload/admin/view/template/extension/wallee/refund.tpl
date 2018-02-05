<?php echo $header; ?>
<?php echo $column_left; ?>
<?php

function echoAmount($amount, $currency_decimals){
	echo number_format($amount, $currency_decimals, '.', '');
}
?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<a href="<?php echo $cancel ?>" data-toggle="tooltip"
					title="<?php echo htmlspecialchars($button_cancel) ?>"
					class="btn btn-default"><i class="fa fa-reply"></i></a>
			</div>
			<h1><?php echo $heading_refund ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) : ?>
				<li><a href="<?php echo $breadcrumb['href'] ?>"><?php echo $breadcrumb['text'] ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
    <?php if ($fixed_tax) : ?>
    <div class="alert alert-warning">
			<i class="fa fa-exclamation-circle"></i> <?php echo $fixed_tax; ?>
	</div>
	<?php endif;?>
    <?php if ($error_warning) : ?>
    <div class="alert alert-danger">
			<i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
   	<?php elseif ($success) : ?>
     <div class="alert alert-success">
			<i class="fa fa-exclamation-circle"></i> <?php echo $success; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
    <?php endif; ?>
    <div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<i class="fa fa-pencil"></i> <?php echo $heading_refund; ?></h3>
			</div>
			<div class="panel-body">

				<h2><?php echo $entry_refund; ?></h2>
				<p><?php echo $description_refund; ?></p>
				<form action="<?php echo $refund_action; ?>" method="POST"
					class="wallee-line-item-grid " id="completion-form">

					<table class="table table-bordered table-hover">
						<thead class="thead-default">
							<tr>
								<td></td>
								<td><?php echo $entry_item; ?></td>
								<td><?php echo $entry_tax; ?></td>
								<td><?php echo $entry_quantity ?></td>
								<td><?php echo $entry_unit_amount; ?></td>
								<td><?php echo $entry_amount; ?></td>
							</tr>
						</thead>

						<tbody>
						<?php foreach ($line_items as $index => $line_item): ?>
							
							<tr id="line-item-row-<?php echo $index ?>" class="line-item-row"
								data-line-item-index="<?php echo $index; ?>">
								<td>
								<?php
							switch ($line_item->getType()) {
								case 'PRODUCT':
									?><i class="fa fa-tag fa-fw" data-toggle="tooltip" title=""
									data-original-title="<?php echo $type_product; ?>"></i><?php
									break;
								case 'SHIPPING':
									?><i class="fa fa-truck fa-fw" data-toggle="tooltip" title=""
									data-original-title="<?php echo $type_shipping; ?>"></i><?php
									break;
								case 'FEE':
									?><i class="fa fa-usd fa-fw" data-toggle="tooltip" title=""
									data-original-title="<?php echo $type_fee; ?>"></i><?php
									break;
								case 'DISCOUNT':
									?><i class="fa fa-percent fa-fw" data-toggle="tooltip" title=""
									data-original-title="<?php echo $type_discount; ?>"></i><?php
									break;
								default:
									echo $line_item->getType();
									break;
							}
							?>
							<input type="hidden" name="item[<?php echo $index;?>][id]"
									value="<?php echo $line_item->getUniqueId();?>"> <input
									type="hidden" name="item[<?php echo $index;?>][type]"
									value="<?php echo $line_item->getType();?>">
								</td>
								<td>
									<dl class="row">
										<dt class="col-sm-3"><?php echo $entry_name; ?></dt>
										<dd class="col-sm-9"><?php echo $line_item->getName(); ?></dd>

										<dt class="col-sm-3"><?php echo $entry_sku; ?></dt>
										<dd class="col-sm-9"><?php echo $line_item->getSku(); ?></dd>

										<dt class="col-sm-3"><?php echo $entry_id; ?></dt>
										<dd class="col-sm-9"><?php echo $line_item->getUniqueId(); ?></dd>
									</dl>
								</td>
								<td>
								<?php if (count($line_item->getTaxes()) >= 1) : ?>
									<dl class="row" style="margin-right: 0px">
									<?php foreach ($line_item->getTaxes() as $tax_rate) :?>
										<dt class="col-sm-6"><?php echo $tax_rate->getTitle(); ?></dt>
										<dd class="col-sm-6"><?php echo sprintf("%.2f",$tax_rate->getRate()); ?>%</dd>
									<?php endforeach; ?>
									</dl>
								<?php endif;?>
								</td>
								<td><div class="input-group">
										<input type="number" class="form-control" min="0" step="1"
											name="item[<?php echo $index;?>][quantity]"
											max="<?php echo $line_item->getQuantity(); ?>" value="0"
											aria-describedby="quantity_<?php echo $index?>_addon"
											style="min-width: 5em;"> <span class="input-group-addon"
											id="quantity_<?php echo $index?>_addon"><?php echo $line_item->getQuantity(); ?></span>
									</div></td>

								<td><div class="input-group">
										<input type="number" class="form-control" min="<?php echo min([0, $line_item->getAmountIncludingTax() / $line_item->getQuantity()]); ?>"
											step="<?php echo $currency_step; ?>"
											name="item[<?php echo $index;?>][unit_price]"
											max="<?php echo max([0, $line_item->getAmountIncludingTax() / $line_item->getQuantity()]); ?>"
											value="0"
											aria-describedby="unit_price_<?php echo $index?>_addon"
											style="min-width: 7em;"> <span class="input-group-addon"
											id="unit_price_<?php echo $index?>_addon"><?php echoAmount($line_item->getAmountIncludingTax() / $line_item->getQuantity(), $currency_decimals); ?></span>
									</div></td>

								<td><div class="input-group">
										<input type="text" class="form-control"
											step="<?php echo $currency_step; ?>"
											name="item[<?php echo $index;?>][total]" value="0"
											aria-describedby="price_<?php echo $index?>_addon"
											style="min-width: 5em;" readonly> <span
											class="input-group-addon"
											id="price_<?php echo $index?>_addon"><?php echoAmount($line_item->getAmountIncludingTax(), $currency_decimals); ?></span>
									</div></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="5" class="text-right"><?php echo $entry_total ?>:</td>
								<td id="line-item-total" class="text-right"></td>
							</tr>
						</tfoot>
					</table>

					<div class="text-right">
						<div class="form-check">
							<label class="form-check-label"> <input name="restock" type="checkbox"
								class="form-check-input"> <?php echo $entry_restock; ?>
							</label>
						</div>
					</div>
					<div class="text-right">
						<input type="reset" class="btn btn-info"
							value="<?php echo $button_reset; ?>" />
						<input type="button" class="btn btn-info"
							value="<?php echo $button_full; ?>" id="full-refund"/>
						<input type="submit" class="btn btn-success"
							value="<?php echo $button_refund; ?>"/>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
jQuery().ready(function() {
	Refund.setEmptyError('<?php echo $error_empty_refund; ?>');
	Refund.init();
});
</script>
<?php echo $footer ?>

<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <h1><?php echo $heading_transaction_list; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_transaction_list; ?></h3>
      </div>
      <div class="panel-body">
        <form method="post" action="<?php echo $filterAction; ?>" enctype="multipart/form-data" id="form-order">
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
              	<tr>
	                <td class="text-right">
	                	<a href="<?php echo $sort_id; ?>"
	                	class="<?php if($filters['sort'] == 'id') { echo strtolower($filters['order']); } ?>"
	                	><?php echo $column_id; ?></a>
	                </td>
	                <td class="text-right">
	                    <a href="<?php echo $sort_order_id; ?>"
	                	class="<?php if($filters['sort'] == 'order_id') { echo strtolower($filters['order']); } ?>"
	                	><?php echo $column_order_id; ?></a>
	                </td>
	                <td class="text-right">
	                    <a href="<?php echo $sort_transaction_id; ?>"
	                	class="<?php if($filters['sort'] == 'transaction_id') { echo strtolower($filters['order']); } ?>"
	                	><?php echo $column_transaction_id; ?></a>
	                </td>
	                <td class="text-right">
	                    <a href="<?php echo $sort_space_id; ?>"
	                	class="<?php if($filters['sort'] == 'space_id') { echo strtolower($filters['order']); } ?>"
	                	><?php echo $column_space_id; ?></a>
	                </td>
	                <td class="text-right">
	                    <a href="<?php echo $sort_space_view_id; ?>"
	                	class="<?php if($filters['sort'] == 'space_view_id') { echo strtolower($filters['order']); } ?>"
	                	><?php echo $column_space_view_id; ?></a>
	                </td>
	                <td class="text-right">
	                    <a href="<?php echo $sort_state; ?>"
	                	class="<?php if($filters['sort'] == 'state') { echo strtolower($filters['state']); } ?>"
	                	><?php echo $column_state; ?></a>
	                </td>
	                <td class="text-right">
	                    <span data-toggle="tooltip" title="<?php echo $description_payment_method?>">
		                    <a href="<?php echo $sort_payment_method_id; ?>"
		                	class="<?php if($filters['sort'] == 'payment_method_id') { echo strtolower($filters['order']); } ?>"
		                	><?php echo $column_payment_method; ?></a>
	                	</span>
	                </td>
	                <td class="text-right">
	                    <a href="<?php echo $sort_authorization_amount; ?>"
	                	class="<?php if($filters['sort'] == 'authorization_amount') { echo strtolower($filters['order']); } ?>"
	                	><?php echo $column_authorization_amount; ?></a>
	                </td>
	                <td class="text-right">
	                    <a href="<?php echo $sort_created_at; ?>"
	                	class="<?php if($filters['sort'] == 'created_at') { echo strtolower($filters['order']); } ?>"
	                	><?php echo $column_created; ?></a>
	                </td>
	                <td class="text-right">
	                    <a href="<?php echo $sort_updated_at; ?>"
	                	class="<?php if($filters['sort'] == 'updated_at') { echo strtolower($filters['order']); } ?>"
	                	><?php echo $column_updated; ?></a>
	                </td>
	                <td class="text-right">
	                </td>
                </tr>
                <tr>
	                <td class="text-right">
	                	<input type="text" class="form-control" name="filters[id]" value="<?php if(isset($filters['id'])) { echo $filters['id']; }?>">
	                </td>
	                <td class="text-right">
	                	<input type="text" class="form-control" name="filters[order_id]" value="<?php if(isset($filters['order_id'])) { echo $filters['order_id']; }?>">
	                </td>
	                <td class="text-right">
	                	<input type="text" class="form-control" name="filters[transaction_id]" value="<?php if(isset($filters['transaction_id'])) { echo $filters['transaction_id']; }?>">
	                </td>
	                <td class="text-right">
	                	<input type="text" class="form-control" name="filters[space_id]" value="<?php if(isset($filters['space_id'])) { echo $filters['space_id']; }?>">
	                </td>
	                <td class="text-right">
	                	<input type="text" class="form-control" name="filters[space_view_id]" value="<?php if(isset($filters['space_view_id'])) { echo $filters['space_view_id']; }?>">
	                </td>
	                <td class="text-right">
	                <select class="form-control" name="filters[state]">
	                	<?php foreach ($order_statuses as $status): ?>
	                	<option value="<?php echo $status?>" <?php if(isset($filters['state']) && $filters['state'] == $status) { echo "selected"; } ?>><?php echo $status; ?></option>
	                	<?php endforeach; ?>
	                </select>
	                </td>
	                <td class="text-right">
	                	<input type="text" class="form-control" name="filters[payment_method_id]" value="<?php if(isset($filters['payment_method_id'])) { echo $filters['payment_method_id']; }?>">
	                </td>
	                <td class="text-right">
	                	<input type="text" class="form-control" name="filters[authorization_amount]" value="<?php if(isset($filters['authorization_amount'])) { echo $filters['authorization_amount']; }?>">
	                </td>
	                <td class="text-right">
	                	<input type="text" class="form-control" name="filters[created_at]" value="<?php if(isset($filters['created_at'])) { echo $filters['created_at']; }?>">
	                </td>
	                <td class="text-right">
	                	<input type="text" class="form-control" name="filters[updated_at]" value="<?php if(isset($filters['updated_at'])) { echo $filters['updated_at']; }?>">
	                </td>
	                <td>
	                	<input type="submit" class="form-control btn btn-success" name="filter_submit" value="<?php echo $button_filter; ?>">
	                </td>
                </tr>
              </thead>
        
              <tbody>
                <?php if ($transactions) { ?>
                <?php foreach ($transactions as $transaction) { ?>
                <tr>
                  <td class="text-left"><?php echo $transaction['id']; ?></td>
                  <td class="text-left"><?php echo $transaction['order_id']; ?></td>
                  <td class="text-left"><?php echo $transaction['transaction_id']; ?></td>
                  <td class="text-left"><?php echo $transaction['space_id']; ?></td>
                  <td class="text-left"><?php echo $transaction['space_view_id']; ?></td>
                  <td class="text-left"><?php echo $transaction['state']; ?></td>
                  <td class="text-left">
                  	<?php echo $transaction['payment_method']; ?>
                  </td>
                  <td class="text-left"><?php echo $transaction['authorization_amount']; ?></td>
                  <td class="text-left"><?php echo $transaction['created_at']; ?></td>
                  <td class="text-left"><?php echo $transaction['updated_at']; ?></td>
                  <td class="text-left"><a href="<?php echo $transaction['view']; ?>" data-toggle="tooltip" title="<?php echo $button_view; ?>" class="btn btn-info"><i class="fa fa-eye"></i></a></td>
                </tr>
                <?php } ?>
                <?php } else { ?>
                <tr>
                  <td class="text-center" colspan="<?php if ($use_space_view) { echo '10'; } else { echo '9'; } ?>"><?php echo $text_no_results; ?></td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </form>
        <div class="row">
          <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
          <div class="col-sm-6 text-right"><?php echo $results; ?></div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?> 

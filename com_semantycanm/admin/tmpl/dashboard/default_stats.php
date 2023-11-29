
<div class="container mt-5">
	<div class="row">
		<div class="col-md-5">
			<h3>Statistics</h3>
			<ul class="list-group" id="stats">
				<?php
				foreach($this->statistics as $stat): ?>
					<li class="list-group-item" data-groupid="<?php echo $stat->id; ?>"><?php echo $stat->recipient; ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>


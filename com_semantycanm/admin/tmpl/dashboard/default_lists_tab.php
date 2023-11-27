
<div class="container mt-5">
	<div class="row">
		<div class="col-md-5">
			<h3>Available User Groups</h3>
			<ul class="list-group" id="availableGroups">
				<?php
				$usergroups = $this->usergroups;
                foreach($usergroups as $group): ?>
					<li class="list-group-item" data-groupid="<?php echo $group->id; ?>"><?php echo $group->title; ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<div class="row mt-4">
		<div class="col-md-12">
			<button class="btn btn-primary" id="createListBtn">Create Mailing List</button>
            <label for="mailingListName"></label><input type="text" class="form-control mt-3" id="mailingListName" placeholder="Mailing List Name" style="display:none;">
		</div>
	</div>
	<div class="row mt-4">
		<div class="col-md-12"  style="height: 400px !important; overflow-y: auto;">
			<h3>Mailing Lists</h3>
			<ul class="list-group" id="mailingLists">
				<?php
				    $mailingLists = $this->mailingLists;
                    foreach($mailingLists as $listName): ?>
					<li class="list-group-item">
						<span class="list-name"><?php echo $listName->name; ?></span>
                        <button class="btn btn-danger btn-sm btn-float-right removeListBtn" style="float: right;">Remove</button>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>


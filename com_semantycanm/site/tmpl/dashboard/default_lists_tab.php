
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
		<div class="col-md-5 offset-md-2">
			<h3>Selected User Groups</h3>
			<ul class="list-group dropzone" id="selectedGroups"></ul>
		</div>
	</div>
	<div class="row mt-4">
		<div class="col-md-12">
			<button class="btn btn-primary" id="createListBtn">Create Mailing List</button>
            <label for="mailingListName"></label><input type="text" class="form-control mt-3" id="mailingListName" placeholder="Mailing List Name" style="display:none;">
		</div>
	</div>
	<div class="row mt-4">
		<div class="col-md-12">
			<h3>Mailing Lists</h3>
			<ul class="list-group" id="mailingLists">
				<?php
				    $mailingLists = $this->mailingLists;
                    foreach($mailingLists as $listName): ?>
					<li class="list-group-item">
						<span class="list-name"><?php echo $listName->name; ?></span>
						<button class="btn btn-danger btn-sm float-right removeListBtn">Remove</button>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>


<script>
    $(document).ready(function() {
        // Drag and Drop Functionality
        $("#availableGroups, #selectedGroups").sortable({
            connectWith: ".list-group",
            items: ".list-group-item",
            dropOnEmpty: true,
            start: function() {
                // Change the cursor to "grabbing" when dragging starts
                $('body').css('cursor', 'grabbing');
            },
            stop: function() {
                // Revert the cursor back to default when dragging stops
                $('body').css('cursor', 'auto');
            }
        }).disableSelection();
        $('#createListBtn').click(function(){
            $('#mailingListName').slideToggle();
        });

        $(document).on('click', '.removeListBtn', function() {
            var listName = $(this).siblings('.list-name').text().trim();
            var listItem = $(this).parent();

            // Send AJAX request to remove the list
            $.post('removeMailingList.php', { name: listName }, function(response) {
                console.log(response);
                if (response.success) {
                    listItem.remove();
                } else {
                    alert('Error: ' + response.message);
                }
            }, 'json');
        });

        $('#mailingListName').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                var groupName = $(this).val();
                saveMailingList(groupName);
            }
        });
    });

    function saveMailingList(name) {
        var selectedGroups = [];
        $('#selectedGroups .list-group-item').each(function(){
            selectedGroups.push($(this).data('groupid'));
        });

        // Endpoint saveMailingList.php handles the XML file creation and save.
        $.post('saveMailingList.php', { name: name, groups: selectedGroups }, function(response) {
            //console.log(response);
            if (response.success) {
                var newList = $(
                    '<li class="list-group-item">' +
                    '<span class="list-name">' + name + '</span>' +
                    '<button class="btn btn-danger btn-sm float-right removeListBtn">Remove</button>' +
                    '</li>'
                );
                $('#mailingLists').append(newList);
                $('#mailingListName').val(''); // Clear the input field
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json'); // Expecting a JSON response
    }
</script>
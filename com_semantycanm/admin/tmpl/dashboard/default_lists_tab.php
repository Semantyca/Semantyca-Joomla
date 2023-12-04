<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <h3>Available User Groups</h3>
            <ul class="list-group" id="available-groups">
				<?php
				foreach ($this->usergroups as $group): ?>
                    <li class="list-group-item" <?php echo 'id="' . $group->id . '"'; ?>>
						<?php echo $group->title; ?></li>
				<?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-6">
            <h3>Selected User Groups</h3>
            <ul id="selected-groups" class="list-group">

            </ul>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="row">
                <div class="input-group gap-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="mailingListName" placeholder="Mailing List Name" required>
                    </div>
                    <div class="invalid-feedback">
                        Please enter Mailing list name.
                    </div>
                    <div class="col-md-2">
                        <button id="add-group" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12" style="height: 400px !important; overflow-y: auto;">
            <h3>Mailing Lists</h3>
            <ul class="list-group" id="mailingLists">
				<?php
				foreach ($this->mailingLists as $listName): ?>
                    <li class="list-group-item" <?php echo 'id="' . $listName->id . '"'; ?>>
                        <span class="list-name"><?php echo $listName->name; ?></span>
                        <button id="remove-group" class="btn btn-danger btn-sm btn-float-right removeListBtn"
                                style="float: right;">Remove
                        </button>
                    </li>
				<?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>


<script>
    let allGroups = document.getElementById('available-groups');
    let selectedGroups = document.getElementById('selected-groups');
    let sortableGroupsList = Sortable.create(allGroups, {
        group: {
            name: 'shared',
            pull: 'clone',
            nut: false
        },
        animation: 150,
        sort: false
    });

    sortableGroupsList.option("onEnd", function (evt) {
        let draggedElement = evt.item;
        let duplicate = Array.from(selectedGroups.children).some(li => {
            return li.dataset.id === draggedElement.id;
        });
        if (!duplicate) {
            let newLiEntry = document.createElement('li');
            newLiEntry.textContent = draggedElement.textContent;
            newLiEntry.dataset.id = draggedElement.id;
            //TODO it needs to be styled
            // newLiEntry.dataset.id = draggedElement.id;
            // newLiEntry.dataset.title = draggedElement.title;
            newLiEntry.className = "list-group-item";
            newLiEntry.addEventListener("click", function () {
                this.parentNode.removeChild(this);
            });
            selectedGroups.appendChild(newLiEntry);

        }
    });

    $(document).ready(function() {
        $('#add-group').click(function(e) {
            e.preventDefault();

            var mailingListName = $('#mailingListName').val();

            if (mailingListName === '') {
                $('#mailingListName').attr('has-validation', 'has-validation');
                return;
            }

            $.ajax({
                url: 'index.php?option=com_semantycanm&task=mailinglist.save',
                type: 'POST',
                data: {
                    'mailingListName': mailingListName
                },
                success: function(response) {
                    console.log(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        });
    });

    jQuery(document).ready(function ($) {
        $('.removeListBtn').click(function () {
            const id = $(this).closest('li').attr('id');
            $.ajax({
                url: 'index.php?option=com_semantycanm&task=mailinglist.delete&ids=' + id,
                type: 'DELETE',
                success: function (response) {
                    console.log(id + " " + response);
                    $('#' + id).remove();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        });
    });

</script>

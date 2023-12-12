<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <h3>Available User Groups</h3>
            <ul class="list-group" id="availableGroups">
				<?php
				foreach ($this->usergroups as $group): ?>
                    <li class="list-group-item" <?php echo 'id="' . $group->id . '"'; ?>>
						<?php echo $group->title; ?></li>
				<?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-6">
            <h3>Selected User Groups</h3>
            <ul id="selectedGroups" class="list-group">

            </ul>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <form class="row needs-validation" novalidate>
                <div class="input-group gap-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control has-validation" id="mailingListName"
                               placeholder="Mailing List Name" required>
                    </div>
                    <div class="invalid-feedback">
                        Please enter Mailing list name.
                    </div>
                    <div class="col-md-2">
                        <button id="addGroup" class="btn btn-success btn"><?php echo JText::_('SAVE'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12" style="height: 400px !important; overflow-y: auto;">
            <div class="header-container">
                <h3>Mailing Lists</h3>
                <div id="listSpinner" class="spinner-border text-info spinner-grow-sm mb-2" role="status" style="display: none;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
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

    $(document).ready(function () {
        $('#addGroup').click(function (e) {
            e.preventDefault();

            const mailingListName = $('#mailingListName').val();

            if (mailingListName === '') {
                alert("Mailing list cannot be empty")
                //TODO it needs boostrap validation
                return;
            }

            const listItems = $('#selectedGroups li').map(function () {
                return $(this).text();
            }).get();

            if (listItems.length === 0) {
                alert('The list is empty.');
                return;
            }
            showSpinner('listSpinner');
            $.ajax({
                url: 'index.php?option=com_semantycanm&task=mailinglist.add',
                type: 'POST',
                data: {
                    'mailinglistname': mailingListName,
                    'mailinglists': listItems.join(',')
                },
                success: function (response) {
                    console.log(response);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                },
                complete: function() {
                    hideSpinner('listSpinner');
                }
            });
        });
        $('.removeListBtn').click(function () {
            const id = $(this).closest('li').attr('id');
            showSpinner('listSpinner');
            $.ajax({
                url: 'index.php?option=com_semantycanm&task=mailinglist.delete&ids=' + id,
                type: 'DELETE',
                success: function (response) {
                    console.log(id + " " + response);
                    $('#' + id).remove();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                },
                complete: function() {
                    hideSpinner('listSpinner');
                }
            });
        });
    });

    const elementCreator = function(draggedElement) {
        let newLiEntry = document.createElement('li');
        newLiEntry.textContent = draggedElement.textContent;
        newLiEntry.dataset.id = draggedElement.dataset.id;
        newLiEntry.className = "list-group-item";
        return newLiEntry;
    };

    dragAndDropSet($('#availableGroups')[0], $('#selectedGroups')[0], elementCreator);



</script>

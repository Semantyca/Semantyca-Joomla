<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">

            <h3><?php echo JText::_('AVAILABLE_USER_GROUPS'); ?></h3>
            <div class="col-md-12 dragdrop-list">
                <ul class="list-group" id="availableGroups">
					<?php
					foreach ($this->usergroups as $group): ?>
                        <li class="list-group-item" <?php echo 'id="' . $group->id . '"'; ?>>
							<?php echo $group->title; ?></li>
					<?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <h3><?php echo JText::_('SELECTED_USER_GROUPS'); ?></h3>
            <div class="col-md-12 dragdrop-list">
                <ul id="selectedGroups" class="list-group">

                </ul>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <form class="row needs-validation" novalidate>
                <div class="input-group gap-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="mailingListName"
                               placeholder="Mailing List Name" required>
                        <div class="invalid-feedback">
		                    <?php echo JText::_('VALIDATION_EMPTY_MAILING_LIST'); ?>
                        </div>
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
                <h3><?php echo JText::_('MAILING_LISTS'); ?></h3>
                <div id="mailingListSpinner" class="spinner-border text-info spinner-grow-sm mb-2" role="status"
                     style="display: none;">
                    <span class="visually-hidden"><?php echo JText::_('LOADING'); ?></span>
                </div>
            </div>
            <table class="table">
                <thead>
                <tr class="d-flex">
                    <th class="col-1">
                        <button class="btn btn-outline-secondary refresh-button" type="button"
                                id="refreshMailingListButton">
                            <img src="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>administrator/components/com_semantycanm/assets/images/refresh.png"
                                 alt="Refresh" class="refresh-icon">
                        </button>
                    </th>
                    <th class="col-5"><?php echo JText::_('NAME'); ?></th>
                    <th class="col-3"><?php echo JText::_('REG_DATE'); ?></th>
                    <th class="col-3"></th>
                </tr>
                </thead>
                <tbody id="mailingList">
            </table>
        </div>
    </div>
</div>


<script>

    document.addEventListener('DOMContentLoaded', function () {
        const mailingListTable = document.getElementById("mailingList");

        if (initialMailingListData) {
            mailingListTable.appendChild(composeMailingListEntry(initialMailingListData));
        }

        document.getElementById('nav-list-tab').addEventListener('shown.bs.tab', () => {
            refreshMailingList();
        });

        document.getElementById('refreshMailingListButton').addEventListener('click', () => {
            refreshMailingList();
        });

        document.getElementById('addGroup').addEventListener('click', function (e) {
            e.preventDefault();
            const mailingListName = document.getElementById('mailingListName').value;
            if (mailingListName === '') {
                showBootstrapAlert("Mailing list name cannot be empty", "warning");
                return;
            }

            const listItems = $('#selectedGroups li').map(function () {
                return $(this).text();
            }).get();

            if (listItems.length === 0) {
                showBootstrapAlert('The list is empty.', 'warning');
                return;
            }

            showSpinner('listSpinner');
            $.ajax({
                url: 'index.php?option=com_semantycanm&task=MailingList.add',
                type: 'POST',
                data: {
                    'mailinglistname': mailingListName,
                    'mailinglists': listItems.join(',')
                },
                success: function (response) {
                    document.getElementById('mailingListName').value = '';
                    const newRow = createMailingListRow(response.data);
                    const tableBody = document.getElementById('mailingList');
                    tableBody.insertBefore(newRow, tableBody.firstChild);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                },
                complete: function () {
                    hideSpinner('mailingListSpinner');
                }
            });
        });
    });

    document.getElementById('addGroup').addEventListener('click', function (e) {
        e.preventDefault();
        const mailingListName = document.getElementById('mailingListName');
        if (mailingListName.value === '') {
            mailingListName.classList.add('is-invalid');
        } else {
            mailingListName.classList.remove('is-invalid');
        }
    });

    document.getElementById('mailingListName').addEventListener('input', function () {
        if (this.value !== '') {
            this.classList.remove('is-invalid');
        }
    });


    function refreshMailingList() {
        showSpinner('mailingListSpinner');
        $.ajax({
            url: 'index.php?option=com_semantycanm&task=MailingList.findall',
            type: 'GET',
            success: function (response) {
                console.log(response);
                if (response.success && response.data) {
                    const mailingList = document.getElementById('mailingList');
                    mailingList.replaceChildren(composeMailingListEntry(response.data));
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('Error:', textStatus, errorThrown);
            },
            complete: function () {
                hideSpinner('mailingListSpinner');
            }
        });
    }

    function composeMailingListEntry(data) {
        const fragment = document.createDocumentFragment();
        data.forEach(entry => {
            fragment.appendChild(createMailingListRow(entry));
        });
        return fragment;
    }

    function createMailingListRow(entry) {
        const tr = document.createElement('tr');
        tr.className = 'list-group-item d-flex';
        tr.setAttribute('data-id', entry.id);
        const tdCheckbox = document.createElement('td');
        tdCheckbox.className = 'col-1';
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'selectedItems[]';
        checkbox.value = entry.id;
        tdCheckbox.appendChild(checkbox);
        const tdName = document.createElement('td');
        tdName.className = 'col-5';
        tdName.textContent = entry.name;

        const tdRegDate = document.createElement('td');
        tdRegDate.className = 'col-3';
        tdRegDate.textContent = entry.reg_date;

        const tdButton = document.createElement('td');
        tdButton.className = 'col-3';
        const button = document.createElement('button');
        button.className = 'btn btn-danger btn-sm removeMailingListBtn';
        button.textContent = removeButtonText;
        button.addEventListener('click', deleteRowHandler);
        tdButton.appendChild(button);
        tr.appendChild(tdCheckbox);
        tr.appendChild(tdName);
        tr.appendChild(tdRegDate);
        tr.appendChild(tdButton);
        return tr;
    }

    function attachDeleteListenerToButton(button) {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            const id = row.getAttribute('data-id');
            showSpinner('mailinListSpinner');

            $.ajax({
                url: 'index.php?option=com_semantycanm&task=MailingList.delete&ids=' + id,
                type: 'DELETE',
                success: function (response) {
                    if (row) {
                        row.remove();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Error:', textStatus, errorThrown);
                },
                complete: function () {
                    hideSpinner('mailingListSpinner');
                }
            });
        });
    }

    const deleteRowHandler = function () {
        const row = this.closest('tr');
        const id = row.getAttribute('data-id');
        showSpinner('mailinListSpinner');

        $.ajax({
            url: 'index.php?option=com_semantycanm&task=MailingList.delete&ids=' + id,
            type: 'DELETE',
            success: function (response) {
                if (row) {
                    row.remove();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error:', textStatus, errorThrown);
            },
            complete: function () {
                hideSpinner('mailingListSpinner');
            }
        });
    };


    const elementCreator = function (draggedElement) {
        let newLiEntry = document.createElement('li');
        newLiEntry.textContent = draggedElement.textContent;
        newLiEntry.dataset.id = draggedElement.dataset.id;
        newLiEntry.className = "list-group-item";
        return newLiEntry;
    };

    dragAndDropSet($('#availableGroups')[0], $('#selectedGroups')[0], elementCreator);


</script>

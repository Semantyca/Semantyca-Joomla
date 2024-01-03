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
            <div class="header-container d-flex justify-content-between align-items-center">
                <h3><?php echo JText::_('MAILING_LISTS'); ?></h3>
                <div id="listSpinner" class="spinner">
                    <img src="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>administrator/components/com_semantycanm/assets/images/spinner.svg"
                         alt="Loading" class="spinner-icon">
                </div>
                <div>
                    <input type="hidden" id="totalInMailingList" value="0"/>
                    <input type="hidden" id="currentInMailingList" value="1"/>
                </div>
	            <?php include(__DIR__ . '/../pagination.php'); ?>
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

        document.getElementById('nav-list-tab').addEventListener('shown.bs.tab', () => refreshMailingList());
        document.getElementById('refreshMailingListButton').addEventListener('click', () => refreshMailingList());
        document.getElementById('goToFirstPage').addEventListener('click', () => refreshMailingList(1));
        document.getElementById('goToPreviousPage').addEventListener('click', () => goToPreviousPage());
        document.getElementById('goToNextPage').addEventListener('click', () => goToNextPage());
        document.getElementById('goToLastPage').addEventListener('click', () => goToLastPage());

        document.getElementById('addGroup').addEventListener('click', function () {
            const mailingListName = document.getElementById('mailingListName').value;
            if (mailingListName === '') {
                showAlertBar("Mailing list name cannot be empty", "warning");
                return;
            }

            const listItems = Array.from(document.querySelectorAll('#selectedGroups li')).map(li => li.textContent);

            if (listItems.length === 0) {
                showAlertBar('The list is empty.', 'warning');
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
                    showAlertBar(errorThrown, "error");
                },
                complete: function () {
                    hideSpinner('listSpinner');
                }
            });
        });
    });

    document.getElementById('addGroup').addEventListener('click', function (e) {
        const mailingListName = document.getElementById('mailingListName');
        if (mailingListName.value === '') {
            mailingListName.classList.add('is-invalid');
            setTimeout(function () {
                mailingListName.classList.remove('is-invalid');
            }, 5000);
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
        showSpinner('listSpinner');
        $.ajax({
            url: 'index.php?option=com_semantycanm&task=MailingList.findall',
            type: 'GET',
            success: function (response) {
                if (response.success && response.data) {
                    const mailingList = document.getElementById('mailingList');
                    mailingList.replaceChildren(composeMailingListEntry(response.data));
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showAlertBar(errorThrown, "error");
            },
            complete: function () {
                hideSpinner('listSpinner');
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
        tdCheckbox.appendChild(createRowCheckBox(entry.id));

        const tdName = document.createElement('td');
        tdName.className = 'col-5';
        tdName.textContent = entry.name;

        const tdRegDate = document.createElement('td');
        tdRegDate.className = 'col-3';
        tdRegDate.textContent = entry.reg_date;

        const tdButton = document.createElement('td');
        tdButton.className = 'col-3 d-flex justify-content-end align-items-center';
        const editButton = createRowButton('Edit', 'btn btn-success btn-sm', editRowHandler);
        tdButton.appendChild(editButton);
        editButton.style.marginRight = '10px';
        const removeButton = createRowButton('Remove', 'btn btn-danger btn-sm', deleteRowHandler);
        tdButton.appendChild(removeButton);

        tr.appendChild(tdCheckbox);
        tr.appendChild(tdName);
        tr.appendChild(tdRegDate);
        tr.appendChild(tdButton);

        return tr;
    }

    const editRowHandler = function () {
        const row = this.closest('tr');
        const id = row.getAttribute('data-id');
        showSpinner('listSpinner');

        $.ajax({
            url: 'index.php?option=com_semantycanm&task=MailingList.find&detailed=1&id=' + id,
            type: 'GET',
            success: function (response) {
                console.log(response.data)
                //document.getElementById('mailingListName').value = response.data.name;
                showAlertBar('the feature is not available yet', "warning");
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showAlertBar(errorThrown, "error");
            },
            complete: function () {
                hideSpinner('listSpinner');
            }
        });
    };

    const deleteRowHandler = function () {
        const row = this.closest('tr');
        const id = row.getAttribute('data-id');
        showSpinner('listSpinner');

        $.ajax({
            url: 'index.php?option=com_semantycanm&task=MailingList.delete&ids=' + id,
            type: 'DELETE',
            success: function () {
                if (row) {
                    row.remove();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showAlertBar(errorThrown, "error");
            },
            complete: function () {
                hideSpinner('listSpinner');
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

    dragAndDropSet(document.getElementById('availableGroups'), document.getElementById('selectedGroups'), elementCreator);




</script>

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

        <div class="header-container d-flex justify-content-between align-items-center">
            <h3><?php echo JText::_('MAILING_LISTS'); ?></h3>
            <div id="listSpinner" class="spinner">
                <img src="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>administrator/components/com_semantycanm/assets/images/spinner.svg"
                     alt="Loading" class="spinner-icon">
            </div>
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="color: gray; display: flex; gap: 5px; align-items: center;">
                    <label for="totalMailingList">Total:</label><input type="text" id="totalMailingList" value="0"
                                                                       readonly
                                                                       style="width: 30px; border: none; background-color: transparent; color: inherit;"/>
                    <label for="currentMailingList">Page:</label><input type="text" id="currentMailingList" value="1"
                                                                        readonly
                                                                        style="width: 20px; border: none; background-color: transparent; color: inherit;"/>
                    <label for="maxMailingList">of</label><input type="text" id="maxMailingList" value="1" readonly
                                                                 style="width: 30px; border: none; background-color: transparent; color: inherit;"/>
                </div>
                <div class="pagination-container mb-3 me-2">
                    <a class="btn btn-primary btn-sm" href="#"
                       id="firstPageMailingList"><?php echo JText::_('FIRST'); ?></a>
                    <a class="btn btn-primary btn-sm" href="#"
                       id="previousPageMailingList"><?php echo JText::_('PREVIOUS'); ?></a>
                    <a class="btn btn-primary btn-sm" href="#"
                       id="nextPageMailingList"><?php echo JText::_('NEXT'); ?></a>
                    <a class="btn btn-primary btn-sm" href="#"
                       id="lastPageMailingList"><?php echo JText::_('LAST'); ?></a>
                </div>
            </div>

        </div>
        <div class="col-md-12" style="height: 400px; overflow-y: auto;">
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
        refreshMailingList(1)
        document.getElementById('nav-list-tab').addEventListener('shown.bs.tab', () => refreshMailingList(1));

        document.getElementById('refreshMailingListButton').addEventListener('click', () => refreshMailingList(1));

        new Pagination('MailingList', refreshMailingList);

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
                    debugger;
                    document.getElementById('mailingListName').value = '';
                    const newRow = createMailingListRow(response.data);
                    const tableBody = document.getElementById('mailingList');
                    tableBody.insertBefore(newRow, tableBody.firstChild);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('Error:', textStatus, errorThrown);
                    debugger;
                    showAlertBar(errorThrown);

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


    function refreshMailingList(currentPage) {
        showSpinner('listSpinner');

        $.ajax({
            url: 'index.php?option=com_semantycanm&task=MailingList.findall&page=' + currentPage + '&limit=' + ITEMS_PER_PAGE,
            type: 'GET',
            success: function (response) {
                if (response.success && response.data) {
                    console.log("data", response.data)
                    document.getElementById('totalMailingList').value = response.data.count;
                    document.getElementById('currentMailingList').value = response.data.current;
                    document.getElementById('maxMailingList').value = response.data.maxPage;
                    document.getElementById('mailingList').replaceChildren(composeMailingListEntry(response.data.docs));
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showAlertBar(errorThrown);
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
                showAlertBar(errorThrown);
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
                showAlertBar(errorThrown);
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

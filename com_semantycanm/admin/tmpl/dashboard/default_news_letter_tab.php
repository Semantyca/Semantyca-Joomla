<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 ">
            <div class="header-container">
                <h3><?php echo JText::_('AVAILABLE_LISTS'); ?></h3>
            </div>
            <div class="col-md-12 dragdrop-list">
                <ul class="list-group" id="availableListsUL">
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <h3><?php echo JText::_('SELECTED_LISTS'); ?></h3>
            <div class="col-md-12 dragdrop-list">
                <ul class="dropzone list-group" id="selectedLists"></ul>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mt-5 submitarea">
        <div class="col-md-12">
			<?php if (!empty($response)): ?>
                <div class="alert alert-info">
					<?= htmlspecialchars($response, ENT_QUOTES, 'UTF-8'); ?>
                </div>
			<?php endif; ?>
            <h2 class="mb-4"><?php echo JText::_('SEND_NEWSLETTER'); ?></h2>
            <input type="hidden" id="currentNewsletterId" name="currentNewsletterId" value="">
            <input type="hidden" id="hiddenSelectedLists" name="selectedLists" value="">
            <div class="form-group">
                <label for="testEmails"><?php echo JText::_('TEST_ADDRESS'); ?></label>
                <input type="text" class="form-control" id="testEmails" name="testEmails">
            </div>
            <div class="form-group">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="subject" name="subject" required placeholder="Subject"
                           aria-label="Subject" aria-describedby="button-addon2">
                    <button class="btn btn-outline-secondary" type="button" id="addSubjectBtn"
                            style="margin: 5px;"><?php echo JText::_('FETCH_SUBJECT'); ?>
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label for="messageContent"><?php echo JText::_('MESSAGE_CONTENT'); ?></label>
                <textarea class="form-control" id="messageContent" name="messageContent" rows="10" required
                          readonly></textarea>
            </div>
            <button type="button" class="btn btn-primary" id="sendNewsletterBtn" name="action"
                    value="send"><?php echo JText::_('SEND_NEWSLETTER'); ?>
            </button>
            <button type="button" class="btn btn-secondary"
                    id="saveNewsletterBtn"><?php echo JText::_('SAVE_NEWSLETTER'); ?></button>
            <button type="button" class="btn btn-secondary" id="toggleEditBtn"><?php echo JText::_('EDIT'); ?></button>

        </div>
    </div>
    <div class="row justify-content-center mt-5">
        <div class="col-md-12">
            <div class="header-container d-flex justify-content-between align-items-center">
                <h3 class="mb-4"><?php echo JText::_('NEWSLETTERS_LIST'); ?></h3>
                <div id="newsletterSpinner" class="spinner">
                    <img src="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>administrator/components/com_semantycanm/assets/images/spinner.svg"
                         alt="Loading" class="spinner-icon">
                </div>
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div style="color: gray; display: flex; gap: 5px; align-items: center;">
                        <label for="totalNewsletterList">Total:</label><input type="text" id="totalNewsletterList"
                                                                              value="0" readonly
                                                                              style="width: 30px; border: none; background-color: transparent; color: inherit;"/>
                        <label for="currentNewsletterList">Page:</label><input type="text" id="currentNewsletterList"
                                                                               value="1" readonly
                                                                               style="width: 20px; border: none; background-color: transparent; color: inherit;"/>
                        <label for="maxNewsletterList">of</label><input type="text" id="maxNewsletterList" value="1"
                                                                        readonly
                                                                        style="width: 30px; border: none; background-color: transparent; color: inherit;"/>
                    </div>
                    <div class="pagination-container mb-3 me-2">
                        <a class="btn btn-primary btn-sm" href="#"
                           id="firstPageNewsletterList"><?php echo JText::_('FIRST'); ?></a>
                        <a class="btn btn-primary btn-sm" href="#"
                           id="previousPageNewsletterList"><?php echo JText::_('PREVIOUS'); ?></a>
                        <a class="btn btn-primary btn-sm" href="#"
                           id="nextPageNewsletterList"><?php echo JText::_('NEXT'); ?></a>
                        <a class="btn btn-primary btn-sm" href="#"
                           id="lastPageNewsletterList"><?php echo JText::_('LAST'); ?></a>
                    </div>
                </div>

            </div>
            <div class="table-responsive" style="height: 200px;">
                <table class="table table-fixed">
                    <thead>
                    <?php
                    $refreshIconUrl = \Joomla\CMS\Uri\Uri::root() . "administrator/components/com_semantycanm/assets/images/refresh.png";
                    ?>

                    <tr>
                        <th class="col-1">
                            <button class="btn btn-outline-secondary refresh-button" type="button"
                                    id="refreshNewsLettersButton">
                                <img src="<?php echo $refreshIconUrl; ?>" alt="Refresh" class="refresh-icon">
                            </button>
                        </th>
                        <th><?php echo JText::_('SUBJECT'); ?></th>
                        <th><?php echo JText::_('REGISTERED'); ?></th>
                    </tr>
                    </thead>
                    <tbody id="newsLettersList">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>

    $(document).ready(function () {

        document.querySelector('#nav-newsletters-tab').addEventListener('shown.bs.tab', function () {
            getPageOfMailingList();
            refreshNewsletters(1);
        });

        document.querySelector('#refreshNewsLettersButton').addEventListener('click', function () {
            refreshNewsletters(1);
        });

        new Pagination('NewsletterList', refreshNewsletters);

        document.querySelector('#addSubjectBtn').addEventListener('click', function () {
            $.ajax({
                url: 'index.php?option=com_semantycanm&task=service.getSubject&type=random',
                type: 'GET',
                success: function (response) {
                    console.log(response.data);
                    document.querySelector('#subject').value = response.data;
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    showAlertBar(textStatus + ", " + errorThrown);
                }
            });
        });

        document.getElementById('saveNewsletterBtn').addEventListener('click', function (e) {
            const msgContent = document.getElementById('messageContent').value;
            let subj = document.getElementById('subject').value;
            let listItems = $('#selectedLists li').map(function () {
                return $(this).text();
            }).get();
            const testEmails = $('#testEmails').val().trim();
            if (listItems.length === 0 && testEmails === "") {
                alert('The list is empty.');
                return;
            }

            if (testEmails !== "") {
                listItems = [testEmails];
            }

            if (msgContent === '') {
                alert("Message content is empty. It cannot be saved")
                return;
            }
            if (subj === '') {
                alert("Subject cannot be empty")
                //TODO it needs boostrap validation
                return;
            }

            const url = "/joomla/administrator/index.php?option=com_semantycanm&task=service.sendEmail";
            const headers = new Headers();
            headers.append("Content-Type", "application/x-www-form-urlencoded");
            const data = new URLSearchParams();
            data.append('encoded_body', encodeURIComponent(msgContent));
            data.append('subject', subj);
            data.append('user_group', listItems);

            fetch(url, {
                method: "POST",
                headers: headers,
                body: data
            })
                .then(response => {
                    if (response.status === 200) {
                        alert(JSON.stringify(response.data))
                        refreshNewsletters(1);
                    } else {
                        console.error('Error:', response.status);
                    }
                })
                .catch((error) => console.error('Error:', error));
        });

        document.getElementById('saveNewsletterBtn').addEventListener('click', function (e) {
            const msgContent = document.getElementById('messageContent').value;
            let subj = document.getElementById('subject').value;

            if (msgContent === '') {
                alert("Message content is empty. It cannot be saved")
                //TODO it needs boostrap validation
                return;
            }
            if (subj === '') {
                alert("Subject cannot be empty")
                //TODO it needs boostrap validation
                return;
            }

            $.ajax({
                url: 'index.php?option=com_semantycanm&task=NewsLetter.add',
                type: 'POST',
                data: {
                    'subject': subj,
                    'msg': encodeURIComponent(msgContent),
                },
                success: function (response) {
                    console.log(JSON.stringify(response.data));
                    alert(JSON.stringify(response.data))
                    refreshNewsletters(1);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    showAlertBar(textStatus + ", " + errorThrown);
                }
            });
        });

        document.getElementById('toggleEditBtn').addEventListener('click', function () {
            const messageContent = document.getElementById('messageContent');
            const toggleBtn = document.getElementById('toggleEditBtn');
            if (messageContent.hasAttribute('readonly')) {
                messageContent.removeAttribute('readonly');
                toggleBtn.textContent = 'Read-Only';
            } else {
                messageContent.setAttribute('readonly', 'readonly');
                toggleBtn.textContent = 'Edit';
            }
        });

        $('#savedNewslettersList').dblclick(function (event) {
            const row = event.target.parentNode;
            const id = row.getAttribute('data-id');
            $.ajax({
                url: 'index.php?option=com_semantycanm&task=NewsLetter.find&id=' + id,
                type: 'GET',
                success: function (response) {
                    console.log(JSON.stringify(response.data));
                    const respData = response.data[0];
                    const msgContent = $('#messageContent');
                    msgContent.prop('readonly', false);
                    msgContent.val(decodeURIComponent(respData.message_content));
                    $('#subject').val(respData.subject);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    showAlertBar(textStatus + ", " + errorThrown);
                }
            });
        });

        $('.removeListBtn').click(function () {
            const id = $(this).closest('tr').attr('data-id');
            $.ajax({
                url: 'index.php?option=com_semantycanm&task=NewsLetter.delete&ids=' + id,
                type: 'DELETE',
                success: function (response) {
                    console.log(id + " " + response);
                    $('#' + id).remove();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    showAlertBar(textStatus + ", " + errorThrown);
                }
            });
        });
    });

    const receiverElementCreator = function (draggedElement) {
        let newLiEntry = document.createElement('li');
        newLiEntry.textContent = draggedElement.textContent;
        newLiEntry.dataset.id = draggedElement.id;
        newLiEntry.className = "list-group-item";
        return newLiEntry;
    }

    dragAndDropSet($('#availableListsUL')[0], $('#selectedLists')[0], receiverElementCreator, null);

    function getPageOfMailingList() {
        $.ajax({
            url: 'index.php?option=com_semantycanm&task=MailingList.findall&page=1&limit=' + ITEMS_PER_PAGE,
            type: 'GET',
            success: function (response) {
                if (response.success && response.data) {
                    document.getElementById('availableListsUL').replaceChildren(composeMailingListUl(response.data.docs));
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showErrorBar('MailingList.findall', errorThrown);
            }
        });
    }

    function composeMailingListUl(data) {
        const fragmentForUl = document.createDocumentFragment();
        data.forEach(entry => {
            fragmentForUl.appendChild(createMailingListLi(entry));
        });
        return fragmentForUl;
    }

    function refreshNewsletters(currentPage) {
        showSpinner('newsletterSpinner');
        $.ajax({
            url: 'index.php?option=com_semantycanm&task=NewsLetter.findAll&page=' + currentPage + '&limit=' + ITEMS_PER_PAGE,
            type: 'GET',
            success: function (response) {
                console.log(response);
                if (response.success && response.data) {
                    console.log(response.data);
                    document.getElementById('totalNewsletterList').value = response.data.count;
                    document.getElementById('currentNewsletterList').value = response.data.current;
                    document.getElementById('maxNewsletterList').value = response.data.maxPage;
                    document.getElementById('newsLettersList').innerHTML = composeNewsLettersContent(response.data.docs);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showAlertBar(textStatus + ", " + errorThrown);
            },
            complete: function () {
                hideSpinner('newsletterSpinner');
            }
        });
    }

    function composeNewsLettersContent(data) {
        let html = '';
        data.forEach(function (newsletter) {
            html += '<tr data-id="' + newsletter.id + '">';
            html += '<td class="col-1"><input type="checkbox" value="' + newsletter.id + '"></td>';
            html += '<td>' + newsletter.subject + '</td>';
            html += '<td>' + newsletter.reg_date + '</td>';
            html += '</tr>';
        });
        return html;
    }

</script>
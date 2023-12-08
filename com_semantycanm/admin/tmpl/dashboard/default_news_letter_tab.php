<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 ">
            <h3>Available Lists</h3>
            <ul class="list-group" id="availableListsUL">
				<?php
				$availableLists = $this->mailingLists;
				foreach ($availableLists as $listName): ?>
                    <li class="list-group-item" <?php echo 'id="' . $listName->id . '"'; ?>>
						<?php echo $listName->name; ?>
                    </li>
				<?php endforeach; ?>
            </ul>

        </div>
        <div class="col-md-6">
            <h3>Selected Lists</h3>
            <ul class="dropzone list-group" id="selectedLists"></ul>
        </div>
    </div>

    <div class="row justify-content-center mt-5 submitarea">
        <div class="col-md-12">
			<?php if (!empty($response)): ?>
                <div class="alert alert-info">
					<?= htmlspecialchars($response, ENT_QUOTES, 'UTF-8'); ?>
                </div>
			<?php endif; ?>
            <h2 class="mb-4">Send Newsletter</h2>
            <input type="hidden" id="currentNewsletterId" name="currentNewsletterId" value="">
            <input type="hidden" id="hiddenSelectedLists" name="selectedLists" value="">
            <div class="form-group">
                <label for="testEmails">Test (if it is not empty It will ignore the selected lists):</label>
                <input type="text" class="form-control" id="testEmails" name="testEmails">
            </div>
            <div class="form-group">
                <label for="subject">Subject:</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="form-group">
                <label for="messageContent">Message Content (HTML):</label>
                <textarea class="form-control" id="messageContent" name="messageContent" rows="10" required
                          readonly></textarea>
            </div>
            <button type="button" class="btn btn-primary" id="sendNewsletterBtn" name="action" value="send">Send
                Newsletter
            </button>
            <button type="button" class="btn btn-secondary" id="saveNewsletterBtn">Save Newsletter</button>
            <button type="button" class="btn btn-secondary" id="toggleEditBtn">Edit</button>

        </div>
    </div>
    <div class="row justify-content-center mt-5">
        <div class="col-md-12">
            <h2 class="mb-4">Saved Newsletters</h2>
            <div class="table-responsive" style="height: 200px;">
                <table class="table table-fixed">
                    <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Registered</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody id="savedNewslettersList">
					<?php
					foreach ($this->newsLetters as $newsletter): ?>
                        <tr data-id="<?php echo $newsletter->id ?>">
                            <td><?php echo $newsletter->subject ?></td>
                            <td><?php echo $newsletter->reg_date ?></td>
                            <td>
                                <button id="remove-group" class="btn btn-danger btn-sm btn-float-right removeListBtn"
                                        style="float: right;">Remove
                                </button>
                            </td>
                        </tr>
					<?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>

    $(document).ready(function () {
        $('#sendNewsletterBtn').click(function () {
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

            const url = "/joomla/administrator/index.php?option=com_semantycanm&task=service.sendEmail";
            const bodyContent = $('#messageContent').val();
            const headers = new Headers();
            headers.append("Content-Type", "application/x-www-form-urlencoded");
            const data = new URLSearchParams();
            data.append('encoded_body', encodeURIComponent(bodyContent));
            data.append('subject', $('#subject').val());
            data.append('user_group', listItems);

            fetch(url, {
                method: "POST",
                headers: headers,
                body: data
            })
                .then(response => {
                    if (response.status === 200) {
                        alert(response.data);
                    } else {
                        console.error('Error:', response.status);
                    }
                })
                .catch((error) => console.error('Error:', error));
        });

        $('#saveNewsletterBtn').click(function (e) {
            e.preventDefault();

            const msgContent = $('#messageContent').val();
            const subj = $('#subject').val();

            if (msgContent === '') {
                alert("Message content is empty. It cannot be saved")
                //TODO it needs boostrap validation
                return;
            }

            $.ajax({
                url: 'index.php?option=com_semantycanm&task=newsletter.add',
                type: 'POST',
                data: {
                    'subject': subj,
                    'msg': encodeURIComponent(msgContent),
                },
                success: function (response) {
                    console.log(response);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        });

        $('#toggleEditBtn').click(function () {
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
                url: 'index.php?option=com_semantycanm&task=newsletter.find&id=' + id,
                type: 'GET',
                success: function (response) {
                    console.log(response.data[0].message_content);
                    const respData = response.data[0];
                    const msgContent = $('#messageContent');
                    msgContent.prop('readonly', false);
                    msgContent.val(decodeURIComponent(respData.message_content));
                    $('#subject').val(respData.subject);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        });

        $('.removeListBtn').click(function () {
            const id = $(this).closest('tr').attr('data-id');
            $.ajax({
                url: 'index.php?option=com_semantycanm&task=newsletter.delete&ids=' + id,
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

    let availableLists = document.getElementById('availableListsUL');
    let selectedLists = document.getElementById('selectedLists');
    let sortableAvailableLists = Sortable.create(availableLists, {
        group: {
            name: 'shared',
            pull: 'clone',
            nut: false
        },
        animation: 150,
        sort: false
        //TODO it needs to be added
        //swap: true,
        //swapClass: 'highlight',
    });

    sortableAvailableLists.option("onEnd", function (evt) {
        let draggedElement = evt.item;
        let duplicate = Array.from(selectedLists.children).some(li => {
            return li.dataset.id === draggedElement.id;
        });
        if (!duplicate) {
            let newLiEntry = document.createElement('li');
            newLiEntry.textContent = draggedElement.textContent;
            newLiEntry.dataset.id = draggedElement.id;
            newLiEntry.className = "list-group-item";
            newLiEntry.addEventListener("click", function () {
                this.parentNode.removeChild(this);
            });
            selectedLists.appendChild(newLiEntry);
        } else {
            // selectedLists.style.animation = "flash 1s infinite";
        }
    });

</script>
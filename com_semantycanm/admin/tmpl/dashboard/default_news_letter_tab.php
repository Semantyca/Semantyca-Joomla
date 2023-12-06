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
                <label for="testEmails">Test (if left empty, the newsletter is sent to selected lists):</label>
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
					$availableNewsLetters = $this->newsLetters;
					foreach ($availableNewsLetters as $newsletter): ?>
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
            const listItems = $('#selectedLists li').map(function () {
                return $(this).text();
            }).get();
            const testEmails = $('#testEmails').val().trim();
            if (listItems.length === 0 || !testEmails) {
                alert('The list is empty.');
                return;
            }

            if (testEmails) {
                listItems.push(testEmails);
            }

            const url = "/joomla/administrator/index.php?option=com_semantycanm&task=service.sendEmail";
            const subj = $('#subject').val()
            const headers = new Headers();
            headers.append("Content-Type", "application/x-www-form-urlencoded");
            const body = new URLSearchParams();
            body.append("body", $('#messageContent').val());
            body.append("subject", subj);
            body.append("user_group", listItems);

            fetch(url, {
                method: "POST",
                headers: headers,
                body: body
            })
                .then(response => {
                    if (response.status === 200) {
                        alert("Request was successful");
                    } else {
                        console.error('Error:', response.status);
                    }
                })
                .catch((error) => console.error('Error:', error));
        });
    });

    $(document).ready(function () {
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
                    'msg': msgContent
                },
                success: function (response) {
                    console.log(response);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        });
    });

    $(document).ready(function () {
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
    });

    jQuery(document).ready(function ($) {
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
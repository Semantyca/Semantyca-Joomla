<head>
    <title></title>
    <style>
        #selected-articles {
            min-height: 450px;
            background-color: #f8f9fa;
            border: 2px dashed #adb5bd;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        #selected-articles:empty {
            border: 3px dashed #adb5bd;
        }

        #preview-button {
            background-color: green;
            color: white;
        }

        .list-group-item {
            cursor: grab;
        }

        #articles-list .list-group-item {
            cursor: grab; /* cursor will appear as an open hand when hovering over articles */
        }

        #articles-list .list-group-item:active {
            cursor: grabbing; /* cursor will appear as a closed hand when an article is being dragged */
        }

        .table-fixed thead {
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .availists, .selectlists {
            height: 200px !important;
            overflow-y: auto;
        }


    </style>


</head>

<div class="container">

    <div class="row">
        <div class="col-md-6 availists">
            <h3>Available Lists</h3>
            <ul class="list-group" id="availableListsUL">
		        <?php
		        $availableLists = $this->mailingLists;
		        foreach ($availableLists as $listName): ?>
                    <li class="list-group-item" <?php echo 'id="'.$listName->id.'"'; ?>>
				        <?php echo $listName->name; ?>
                    </li>
		        <?php endforeach; ?>
            </ul>

        </div>
        <div class="col-md-6 selectlists">
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
            <form action="" method="POST">
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
                <div class="form-group" >
                    <label for="messageContent">Message Content (HTML):</label>
                    <textarea class="form-control" id="messageContent" name="messageContent" rows="10"
                              required><?= htmlspecialchars(newsLetters['messageContent'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary" name="action" value="send">Send Newsletter</button>
                <button type="button" class="btn btn-secondary" id="saveNewsletterBtn">Save Newsletter</button>
            </form>

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
                        <th>Date Saved</th>
                    </tr>
                    </thead>
                    <tbody id="savedNewslettersList">
					<?php
					$availableNewsLetters = $this->newsLetters;
					foreach ($availableNewsLetters as $newsletter): ?>
                        <tr data-id="<?php echo $newsletter->id ?>">
                            <td><?php echo $newsletter->subject ?></td>
                            <td><?php echo $newsletter->send_date ?></td>
                        </tr>
					<?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
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
        let selectedLists = document.getElementById('selectedLists');
        let duplicate = Array.from(selectedLists.children).some(li => {
            return li.dataset.id === draggedElement.id;
        });
        if (!duplicate) {
            let newLiEntry = document.createElement('li');
            newLiEntry.textContent = draggedElement.textContent;
            newLiEntry.dataset.id = draggedElement.id;
            newLiEntry.className = "list-group-item";
            newLiEntry.addEventListener("click", function() {
                this.parentNode.removeChild(this);
            });
            selectedLists.appendChild(newLiEntry);
        } else {
           // selectedLists.style.animation = "flash 1s infinite";
        }
    });

</script>
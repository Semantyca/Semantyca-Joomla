<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <div class="header-container">
                <h3><?php echo JText::_('AVAILABLE_ARTICLES'); ?></h3>
                <div id="composerSpinner" class="spinner-border text-info spinner-grow-sm mb-2" role="status"
                     style="display: none;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <input type="text" id="articleSearchInput" class="form-control mb-2" placeholder="Search articles...">
            <ul id="articlesList" class="list-group dragdrop-list-short">
				<?php
				foreach ($this->articlesList as $article): ?>
                    <li class="list-group-item"
						<?php echo 'id="' . $article->id .
							'" title="' . $article->title .
							'"  data-url="' . $article->url .
							'" data-category="' . $article->category .
							'" data-intro="' . rawurlencode($article->introtext) . '"'; ?>>
                        <strong><?php echo $article->category ?></strong><br>'<?php echo $article->title; ?>'
                    </li>
				<?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-6">
            <div class="header-container">
                <h3><?php echo JText::_('SELECTED_ARTICLES'); ?></h3>
            </div>
            <ul id="selectedArticles" class="list-group dragdrop-list"></ul>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="btn-group">
                <button id="resetBtn" class="btn"
                        style="background-color: #152E52; color: white;"><?php echo JText::_('RESET'); ?></button>
                <button id="copyCodeBtn" class="btn btn-info mb-2"><?php echo JText::_('COPY_CODE'); ?></button>
                <button id="nextBtn" class="btn btn-info mb-2"><?php echo JText::_('NEXT'); ?></button>
            </div>
            <label for="outputHtml"></label><textarea id="outputHtml" class="form-control mt-3" rows="20"></textarea>
        </div>
    </div>
</div>

<script>
    let outputHtml = $('#outputHtml');

    $(document).ready(function () {
        document.getElementById('resetBtn').addEventListener('click', function () {
            let sourceList = document.querySelectorAll('#selectedArticles li');
            let targetList = document.getElementById('articlesList');
            sourceList.forEach((li) => {
                let articleElement = createElement(li.id, li.title, li.dataset.url, li.dataset.category, li.dataset.intro);
                if (targetList.firstChild) {
                    targetList.insertBefore(articleElement, targetList.firstChild);
                } else {
                    targetList.appendChild(articleElement);
                }
            });

            outputHtml.value = '';
            $('#outputHtml').trumbowyg('html', '');
            document.getElementById('selectedArticles').innerHTML = '';
        });

        document.getElementById('articleSearchInput').addEventListener('input', function () {
            const delayTime = 1000;
            clearTimeout(this.delayTimeout);
            this.delayTimeout = setTimeout(() => {
                const searchTerm = this.value.toLowerCase();
                fetchArticles(searchTerm);
            }, delayTime);
        });


        $('#copyCodeBtn').click(function () {
            const completeHTML = getFullTemplate(outputHtml.val());
            const tempTextArea = $('<textarea>').val(completeHTML).appendTo('body').select();
            const successful = document.execCommand('copy');
            if (successful) {
                showAlertBar('HTML code copied to clipboard!', "info");
            } else {
                showAlertBar('Failed to copy. Please try again.', "warning");
            }
            tempTextArea.remove();
        });

        $('#nextBtn').click(function () {
            const messageContent = document.getElementById('messageContent');
            messageContent.value = getFullTemplate(outputHtml.val());
            $('#nav-newsletters-tab').tab('show');
        });
    });

    function createElement(id, title, url, category, intro) {
        let li = document.createElement('li');
        li.className = 'list-group-item';
        li.setAttribute('id', id);
        li.setAttribute('title', title);
        li.setAttribute('data-url', url);
        li.setAttribute('data-category', category);
        li.setAttribute('data-intro', intro);
        li.innerHTML = `<strong>${category}</strong><br>${title}`;
        return li;
    }

    const articleElementCreator = function (draggedObj) {
        return createElement(draggedObj.id, draggedObj.title, draggedObj.dataset.url, draggedObj.dataset.category, draggedObj.dataset.intro);
    }

    dragAndDropSet(document.getElementById('articlesList'), document.getElementById('selectedArticles'), articleElementCreator, updateNewsletterContent);

    function fetchArticles(searchTerm) {
        showSpinner('composerSpinner');
        fetch('index.php?option=com_semantycanm&task=Article.search&q=' + encodeURIComponent(searchTerm))
            .then(response => response.json())
            .then(data => {
                const artclLst = document.getElementById('articlesList');
                artclLst.innerHTML = '';
                data.data.forEach(a => {
                    let liElement = createElement(a.id, a.title, a.url, a.category, encodeURIComponent(a.introtext));
                    artclLst.appendChild(liElement);
                });
            })
            .catch(error => {
                showAlertBar(error, "error");
            })
            .finally(() => {
                hideSpinner('composerSpinner');
            });
    }

</script>

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
            <h3><?php echo JText::_('SELECTED_ARTICLES'); ?></h3>
            <div class="col-md-12 dragdrop-list">
                <ul id="selectedArticles" class="list-group">

                </ul>
            </div>
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
            let outputHtml = document.getElementById('outputHtml');
            outputHtml.value = '';
            if (outputHtml._trumbowyg) {
                outputHtml._trumbowyg.empty();
            }
            document.getElementById('selectedArticles').innerHTML = '';
        });

        document.getElementById('articleSearchInput').addEventListener('input', function () {
            const delayTime = 1000;
            clearTimeout(this.delayTimeout);
            this.delayTimeout = setTimeout(() => {
                const searchTerm = this.value.toLowerCase();
                showSpinner('composerSpinner');
                fetch('index.php?option=com_semantycanm&task=Article.search&q=' + encodeURIComponent(searchTerm))
                    .then(response => response.json())
                    .then(data => {
                        const articlesList = document.getElementById('articlesList');
                        articlesList.innerHTML = '';
                        data.data.forEach(article => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item';
                            li.setAttribute('id', article.id);
                            li.setAttribute('title', article.title);
                            li.setAttribute('data-url', article.url);
                            li.setAttribute('data-category', article.category);
                            li.setAttribute('data-intro', encodeURIComponent(article.introtext));
                            li.innerHTML = `<strong>${article.category}</strong><br>${article.title}`;
                            articlesList.appendChild(li);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    })
                    .finally(() => {
                        hideSpinner('composerSpinner');
                    });
            }, delayTime);
        });



        $('#copyCodeBtn').click(function () {
            const completeHTML = getFullTemplate(outputHtml.val());
            const tempTextArea = $('<textarea>').val(completeHTML).appendTo('body').select();
            const successful = document.execCommand('copy');
            if (successful) {
                alert('HTML code copied to clipboard!');
            } else {
                alert('Failed to copy. Please try again.');
            }
            tempTextArea.remove();
        });

        $('#nextBtn').click(function () {
            const messageContent = document.getElementById('messageContent');
            messageContent.value = getFullTemplate(outputHtml.val());
            $('#nav-newsletters-tab').tab('show');
        });
    });

    const articleElementCreator = function (draggedElement) {
        let newLiEntry = document.createElement('li');
        newLiEntry.dataset.id = draggedElement.id;
        newLiEntry.textContent = draggedElement.title;
        newLiEntry.dataset.title = draggedElement.title;
        newLiEntry.dataset.url = draggedElement.dataset.url;
        newLiEntry.dataset.category = draggedElement.dataset.category;
        newLiEntry.dataset.intro = draggedElement.dataset.intro;
        newLiEntry.className = "list-group-item4";
        return newLiEntry;
    }

    dragAndDropSet($('#articlesList')[0], $('#selectedArticles')[0], articleElementCreator, updateNewsletterContent);

</script>

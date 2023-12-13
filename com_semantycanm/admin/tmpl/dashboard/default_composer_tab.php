<?php

defined('_JEXEC') or die;
//require_once __DIR__ . '/template_source_helper.php';
$app      = Joomla\CMS\Factory::getApplication();
$doc      = $app->getDocument();


?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <div class="header-container">
                <h3><?php echo JText::_('AVAILABLE_ARTICLES'); ?></h3>
                <div id="composerSpinner" class="spinner-border text-info spinner-grow-sm mb-2" role="status" style="display: none;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <input type="text" id="articleSearchInput" class="form-control mb-2" placeholder="Search articles...">
            <ul id="articlesList" class="list-group" style="height: 350px !important; overflow-y: auto;">
				<?php
				foreach ($this->articlesList as $article): ?>
                    <li class="list-group-item"
						<?php echo 'id="' . $article->id . '" title="' . $article->title . '"  url="' . $article->url . '" category="' . $article->category . '" intro="' . $article->introtext . '"'; ?>>
                        <strong><?php echo $article->category ?></strong><br><?php echo $article->title; ?>
                    </li>
				<?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-6">
            <h3><?php echo JText::_('SELECTED_ARTICLES'); ?></h3>
            <ul id="selectedArticles" class="list-group">

            </ul>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="btn-group">
                <button id="resetBtn" class="btn" style="background-color: #152E52; color: white;"><?php echo JText::_('RESET'); ?></button>
                <button id="copyCodeBtn" class="btn btn-info mb-2"><?php echo JText::_('COPY_CODE'); ?></button>
                <button id="nextBtn" class="btn btn-info mb-2"><?php echo JText::_('NEXT'); ?></button>
            </div>
            <label for="output-html"></label><textarea id="output-html" class="form-control mt-3" rows="10"></textarea>
        </div>
    </div>
</div>

<script>
    let outputHtml = $('#output-html');

    $(document).ready(function () {
        $('#resetBtn').click(function () {
            outputHtml.val('');
            outputHtml.trumbowyg('html', '')
            $('#selectedArticles').empty();
        });

        $('#articleSearchInput').on('input', function () {
            const searchTerm = $(this).val().toLowerCase();
            if (searchTerm.length >= 3) {
                showSpinner('composerSpinner');
                $.ajax({
                    url: 'index.php?option=com_semantycanm&task=article.search',
                    type: 'GET',
                    data: {
                        q: searchTerm
                    },
                    dataType: 'json',
                    success: function (response) {
                        //console.log(response.data);
                        $('#articlesList').empty();
                        response.data.forEach(article => {
                            $('#articlesList').append(`
                  <li class="list-group-item"
                      id="${article.id}"
                      title="${article.title}"
                      data-url="${article.url}"
                      data-category="${article.category}"
                      data-intro="${article.introtext}">
                      <strong>${article.category}</strong><br>${article.title}
                  </li>
               `);
                        });

                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error('Error: ' + textStatus + ' ' + errorThrown);
                    },
                    complete: function () {
                        hideSpinner('composerSpinner');
                    }
                });
            }
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

    const articleElementCreator = function(draggedElement) {
        debugger
        let newLiEntry = document.createElement('li');
        newLiEntry.dataset.id = draggedElement.id;
        newLiEntry.dataset.title =  draggedElement.title;
        newLiEntry.textContent = draggedElement.attributes.title.nodeValue;
        newLiEntry.dataset.url =  draggedElement.attributes.url.nodeValue;
        newLiEntry.dataset.category = draggedElement.attributes.category.nodeValue;
        newLiEntry.dataset.intro = draggedElement.attributes.intro.nodeValue;
        newLiEntry.className = "list-group-item";
        return newLiEntry;
    }

    dragAndDropSet($('#articlesList')[0], $('#selectedArticles')[0], articleElementCreator, updateNewsletterContent);

</script>

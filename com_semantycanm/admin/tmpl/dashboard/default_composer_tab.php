<div class="container" id="composerSection">
    <div id="loadingSpinner" class="loading-spinner"></div>
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
                <ul ref="articlesListRef" id="articles" class="list-group dragdrop-list-short">
                    <li v-for="article in articles" :key="article.id" class="list-group-item"
                        :id="article.id" :title="article.title" :data-url="article.url"
                        :data-category="article.category" :data-intro="article.intro">
                        <strong>{{ article.category }}</strong><br>{{ article.title }}
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <div class="header-container">
                    <h3><?php echo JText::_('SELECTED_ARTICLES'); ?></h3>
                </div>
                <ul ref="selectedArticlesListRef" id="selectedArticles" class="list-group dragdrop-list">
                    <li v-for="selectedArticle in state.selectedArticles" :key="selectedArticle.id"
                        class="list-group-item"
                        :id="selectedArticle.id" :title="selectedArticle.title" :data-url="selectedArticle.url"
                        :data-category="selectedArticle.category" :data-intro="selectedArticle.intro">
                        <strong>{{ selectedArticle.category }}</strong><br>{{ selectedArticle.title }}
                    </li>
                </ul>
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
                <label for="outputHtml"></label>
                <div ref="composerRef" id="outputHtml" style="border: 1px solid gray">
                </div>
            </div>
        </div>
    </div>
</div>

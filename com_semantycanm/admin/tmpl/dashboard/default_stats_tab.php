<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <div class="header-container d-flex align-items-center">
                <div class="me-auto"> <!-- me-auto class pushes the element to the left -->
                    <h3><?php echo JText::_('STATISTICS'); ?></h3>
                </div>
                <div>
                    <div id="statSpinner" class="spinner-border text-info spinner-grow-sm mb-2" role="status" style="display: none;">
                        <span class="visually-hidden"><?php echo JText::_('LOADING'); ?></span>
                    </div>
                    <input type="hidden" id="total" value="0"/>
                    <input type="hidden" id="current" value="1"/>
                </div>
                <div> <!-- This will now be closer to the "STATISTICS" heading -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <li class="page-item"><a class="page-link" href="#" id="goToFirstPage"><?php echo JText::_('FIRST'); ?></a></li>
                            <li class="page-item"><a class="page-link" href="#" id="goToPreviousPage"><?php echo JText::_('PREVIOUS'); ?></a></li>
                            <li class="page-item"><a class="page-link" href="#" id="goToNextPage"><?php echo JText::_('NEXT'); ?></a></li>
                            <li class="page-item"><a class="page-link" href="#" id="goToLastPage"><?php echo JText::_('LAST'); ?></a></li>
                        </ul>
                    </nav>
                </div>
            </div>


            <table class="table">
                <thead>
                <tr class="d-flex">
                    <th class="col-1">
                        <button class="btn btn-outline-secondary refresh-button" type="button" id="refreshStatsButton">
                            <img src="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>administrator/components/com_semantycanm/assets/images/refresh.png" alt="Refresh" class="refresh-icon">
                        </button>
                    </th>
                    <th class="col-4"><?php echo JText::_('RECIPIENTS'); ?></th>
                    <th class="col-2"><?php echo JText::_('STATUS'); ?></th>
                    <th class="col-3"><?php echo JText::_('SEND_TIME'); ?></th>
                    <th class="col-1"><?php echo JText::_('OPENS'); ?></th>
                    <th class="col-1"><?php echo JText::_('CLICKS'); ?></th>
                    <th class="col-1"><?php echo JText::_('UNSUBS'); ?></th>
                    <th class="col-2"><?php echo JText::_('NEWSLETTER'); ?></th>
                </tr>
                </thead>
                <tbody id="statsList">
            </table>

        </div>
    </div>
</div>



<script>
    const itemsPerPage = 10;

    $(document).ready(function () {
        document.getElementById('nav-stats-tab').addEventListener('shown.bs.tab', () => refreshStats(1));
        document.getElementById('refreshStatsButton').addEventListener('click', () => refreshStats(getCurrentPage()));

        document.getElementById('goToFirstPage').addEventListener('click', () => goToFirstPage());
        document.getElementById('goToPreviousPage').addEventListener('click', () => goToPreviousPage());
        document.getElementById('goToNextPage').addEventListener('click', () => goToNextPage());
        document.getElementById('goToLastPage').addEventListener('click', () => goToLastPage());
    });

    function refreshStats(currentPage) {
        showSpinner('statSpinner');

        currentPage = Math.max(currentPage, 1);
        const totalPages = getTotalPages();
        currentPage = Math.min(currentPage, totalPages);

        $.ajax({
            url: 'index.php?option=com_semantycanm&task=Stat.findAll&page=' + currentPage + '&limit=' + itemsPerPage,
            type: 'GET',
            success: function (response) {
                if (response.success && response.data) {
                    $('#statsList').html(composeStatsContent(response.data.documents));
                    $('#total').val(response.data.total);
                    $('#current').val(currentPage);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('Error:', textStatus, errorThrown);
            },
            complete: function() {
                hideSpinner('statSpinner');
            }
        });
    }


    function getTotalPages(){
        const totalRecords = parseInt($('#total').val());
        const itemsPerPage = 10;
        return Math.ceil(totalRecords / itemsPerPage);
    }


    function getCurrentPage(){
        return parseInt($('#current').val());
    }

    function goToFirstPage() {
        refreshStats(1);
    }

    function goToPreviousPage() {
        const currentPage = getCurrentPage();
        if (currentPage > 1) {
            refreshStats(currentPage - 1);
        }
    }

    function goToNextPage() {
        const currentPage = getCurrentPage();
        const totalPages = getTotalPages();
        if (currentPage < totalPages) {
            refreshStats(currentPage + 1);
        }
    }

    function goToLastPage() {
        const totalPages = getTotalPages();
        refreshStats(totalPages);
    }

    function composeStatsContent(data) {
        let html = '';
        data.forEach(function (stat) {
            html += '<tr class="list-group-item d-flex" data-groupid="' + stat.id + '">';
            html += '<td class="col-1"><input type="checkbox" name="selectedItems[]" value="' + stat.id + '"></td>';
            html += '<td class="col-4">' + stat.recipients + '</td>';
            html += '<td class="col-2">' + getBadge(stat.status) + '</td>';
            html += '<td class="col-3">' + (stat.sent_time ? stat.sent_time : 'N/A') + '</td>';
            html += '<td class="col-1">' + stat.opens + '</td>';
            html += '<td class="col-1">' + stat.clicks + '</td>';
            html += '<td class="col-1">' + stat.unsubs + '</td>';
            html += '<td class="col-2">' + stat.newsletter_id + '</td>';
            html += '</tr>';
        });
        return html;
    }

    function getBadge(status) {
        switch (status) {
            case -1:
                return '<span class="badge bg-danger">error</span>'
            case 1:
                return '<span class="badge bg-warning text-dark">sending</span>'
            case 2:
                return '<span class="badge bg-info text-light">sent</span>'
            case 3:
                return '<span class="badge bg-success">read</span>'
            default:
                return '<span class="badge bg-secondary">unknown</span>'
        }
    }
</script>

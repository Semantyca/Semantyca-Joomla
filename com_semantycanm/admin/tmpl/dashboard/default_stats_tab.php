<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="header-container d-flex justify-content-between align-items-center">
                <h3><?php echo JText::_('STATISTICS'); ?></h3>
                <div id="statSpinner" class="spinner">
                    <img src="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>administrator/components/com_semantycanm/assets/images/spinner.svg"
                         alt="Loading" class="spinner-icon">
                </div>
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div style="color: gray; display: flex; gap: 5px; align-items: center;">
                        <label for="totalStatList">Total:</label>
                        <input type="text" id="totalStatList"
                               value="0" readonly
                               style="width: 30px; border: none; background-color: transparent; color: inherit;"/>
                        <label for="currentStatList">Page:</label>
                        <input type="text" id="currentStatList"
                               value="1" readonly
                               style="width: 20px; border: none; background-color: transparent; color: inherit;"/>
                        <label for="maxStatList">of</label>
                        <input type="text" id="maxStatList" value="1"
                               readonly
                               style="width: 30px; border: none; background-color: transparent; color: inherit;"/>
                    </div>
                    <div class="pagination-container mb-3 me-2">
                        <a class="btn btn-primary btn-sm" href="#"
                           id="firstPageStatList"><?php echo JText::_('FIRST'); ?></a>
                        <a class="btn btn-primary btn-sm" href="#"
                           id="previousPageStatList"><?php echo JText::_('PREVIOUS'); ?></a>
                        <a class="btn btn-primary btn-sm" href="#"
                           id="nextPageStatList"><?php echo JText::_('NEXT'); ?></a>
                        <a class="btn btn-primary btn-sm" href="#"
                           id="lastPageStatList"><?php echo JText::_('LAST'); ?></a>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th style="width: 5%;">
                            <button class="btn btn-outline-secondary refresh-button" type="button"
                                    id="refreshStatsButton">
                                <img src="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>administrator/components/com_semantycanm/assets/images/refresh.png"
                                     alt="Refresh" class="refresh-icon">
                            </button>
                        </th>
                        <th style="width: 20%;"><?php echo JText::_('NEWSLETTER'); ?></th>
                        <th style="width: 10%;"><?php echo JText::_('STATUS'); ?></th>
                        <th style="width: 15%;"><?php echo JText::_('SEND_TIME'); ?></th>
                        <th style="width: 10%;"><?php echo JText::_('RECIPIENTS'); ?></th>
                        <th style="width: 10%;"><?php echo JText::_('OPENS'); ?></th>
                        <th style="width: 10%;"><?php echo JText::_('CLICKS'); ?></th>
                        <th style="width: 10%;"><?php echo JText::_('UNSUBS'); ?></th>
                    </tr>
                    </thead>
                    <tbody id="statsList">
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        document.getElementById('nav-stats-tab').addEventListener('shown.bs.tab', () => refreshStats(1));
        document.getElementById('refreshStatsButton').addEventListener('click', () => refreshStats(1));
        new Pagination('StatList', refreshStats);
    });

    function refreshStats(currentPage) {
        showSpinner('statSpinner');

        $.ajax({
            url: 'index.php?option=com_semantycanm&task=Stat.findAll&page=' + currentPage + '&limit=10',
            type: 'GET',
            success: function (response) {
                if (response.success && response.data) {
                    document.getElementById('totalStatList').value = response.data.count;
                    document.getElementById('currentStatList').value = response.data.current;
                    document.getElementById('maxStatList').value = response.data.maxPage;
                    document.getElementById('statsList').innerHTML = composeStatsContent(response.data.docs);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showAlertBar(textStatus + ", " + errorThrown);
            },
            complete: function () {
                hideSpinner('statSpinner');
            }
        });
    }

    function composeStatsContent(data) {
        let html = '';
        data.forEach(function (stat) {
            html += '<tr data-groupid="' + stat.id + '">';
            html += '<td style="width: 5%;"><input type="checkbox" name="selectedItems[]" value="' + stat.id + '"></td>';
            html += '<td style="width: 20%;">' + stat.newsletter_id + '</td>';
            html += '<td style="width: 10%;">' + getBadge(stat.status) + '</td>';
            html += '<td style="width: 15%;">' + (stat.sent_time ? stat.sent_time : 'N/A') + '</td>';
            html += '<td style="width: 10%;">' + stat.recipients.length + '</td>';
            html += '<td style="width: 10%;">' + stat.opens + '</td>';
            html += '<td style="width: 10%;">' + stat.clicks + '</td>';
            html += '<td style="width: 10%;">' + stat.unsubs + '</td>';
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

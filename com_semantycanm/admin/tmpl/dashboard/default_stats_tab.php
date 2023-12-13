<div class="container mt-5">
    <div class="row">
        <div class="col-md-5">
            <div class="header-container">
                <h3><?php echo JText::_('STATISTICS'); ?></h3>
                <div id="statSpinner" class="spinner-border text-info spinner-grow-sm mb-2" role="status" style="display: none;">
                    <span class="visually-hidden"><?php echo JText::_('LOADING'); ?></span>
                </div>
            </div>

            <table class="table">
                <thead>
                <tr class="d-flex">
                    <th class="col-1">
                        <button class="btn btn-outline-secondary refresh-button" type="button" id="refreshStatsButton">
                            <img src="/joomla/administrator/components/com_semantycanm/assets/images/refresh.png"
                                 alt="Refresh" class="refresh-icon">
                        </button>
                    </th>
                    <th class="col-5"><?php echo JText::_('RECIPIENT'); ?></th>
                    <th class="col-2"><?php echo JText::_('STATUS'); ?></th>
                    <th class="col-3"><?php echo JText::_('SEND_TIME'); ?></th>
                    <th class="col-3"><?php echo JText::_('READING_TIME'); ?></th>
                    <th class="col-3"><?php echo JText::_('NEWSLETTER'); ?></th>
                </tr>
                </thead>
                <tbody id="statsList">
            </table>

        </div>
    </div>
</div>



<script>

    $(document).ready(function () {
        $('#nav-stats-tab').on('shown.bs.tab', function () {
            refreshStats();
        });

        $('#refreshStatsButton').click(function () {
            refreshStats();
        });
    });

    function refreshStats() {
        showSpinner('statSpinner');

        $.ajax({
            url: 'index.php?option=com_semantycanm&task=stat.findAll',
            type: 'GET',
            success: function (response) {
                console.log(response);
                if (response.success && response.data) {
                    $('#statsList').html(composeStatsContent(response.data));
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


    function composeStatsContent(data) {
        let html = '';
        data.forEach(function (stat) {
            html += '<tr class="list-group-item d-flex" data-groupid="' + stat.id + '">';
            html += '<td class="col-1"><input type="checkbox" name="selectedItems[]" value="' + stat.id + '"></td>';
            html += '<td class="col-5">' + stat.recipient + '</td>';
            html += '<td class="col-2">' + getBadge(stat.status) + '</td>';
            html += '<td class="col-3">' + stat.sent_time + '</td>';
            html += '<td class="col-3">' + (stat.reading_time ? stat.reading_time : 'N/A') + '</td>';
            html += '<td class="col-3">' + stat.newsletter_id + '</td>';
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

<div class="container mt-5">
    <div class="row">
        <div class="col-md-5">
            <h3>Statistics</h3>
            <table class="table">
                <tr class="d-flex">
                    <th class="col-6">Recipient</th>
                    <th class="col-4">Status</th>
                    <th class="col-3">Send Time</th>
                    <th class="col-3">Reading Time</th>
                    <th class="col-3">Newsletter</th>
                </tr>
				<?php
				use Semantyca\Component\SemantycaNM\Administrator\Helper\Constants;
				foreach($this->stats as $stat):
					$status = Constants::getStatusText($stat->status);
					$reading_time = $stat->reading_time ? $stat->reading_time : 'N/A';
					?>
                    <tr class="list-group-item d-flex" data-groupid="<?php echo $stat->id; ?>">
                        <td class="col-6"><?php echo $stat->recipient; ?></td>
                        <td class="col-4" style="border-radius: 5px; border: 1px solid coral;" ><?php echo $status; ?></td>
                        <td class="col-3"><?php echo $stat->sent_time; ?></td>
                        <td class="col-3"><?php echo $reading_time; ?></td>
                        <td class="col-3"><?php echo $stat->newsletter_id; ?></td>
                    </tr>
				<?php endforeach; ?>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default no-shadow">
            <div class="col-xs-12">
                <div class="row" style="padding-top: 10px; padding-bottom: 10px">
                    <div class="col-xs-3 text-left"><span class="fa fa-bar-chart fa-lg"></span></div>
                    <div class="col-xs-6 text-center">Recent Payment History</div>
                    <div class="col-xs-3 text-right"></div>
                </div>
            </div>
            <table class="table grey">
                <thead>
                    <tr class="active black">
                        <th width="30"></th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th class="text-center">Paid Method</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for ($i = 0; $i < count($trans); $i++) {
                        echo '<tr ';
                        if ($i % 2 != 0)
                            echo 'class="active"';
                        echo '>'; ?>
                        <td><img src="{{ asset('/img/glass.png') }}" onclick="showTransdetail(<?= $trans[$i]['trans_id']?>)" style="cursor:pointer"></td>
                      <?php
                            if($trans[$i]['trans_type'] == 9) {
                                echo '<td><img src="/img/void.png" class="tooltip_hover" data-toggle="tooltip" title="Voided transaction."></td>';
                            } else if ($trans[$i]['trans_type'] == 10) {
                                echo '<td><img src="/img/void.png" class="tooltip_hover" data-toggle="tooltip" title="Refunded transaction."></td>';
                            } else if ($trans[$i]['trans_type'] == 2) {
                                echo '<td><img src="/img/undored.png" class="tooltip_hover" data-toggle="tooltip" title="Returned transaction."></td>';
                            } else {
                                switch ($trans[$i]['trans_status']) {
                                    case 1:
                                        echo '<td><img src="/img/check.png" class="tooltip_hover" data-toggle="tooltip" title="Approved transaction."></td>';
                                        break;
                                    default:
                                        echo '<td><img src="/img/tiny_cancel.png" data-html="true" class="tooltip_hover" data-toggle="tooltip" title="Errored transaction:<br>' . $trans[$i]['trans_result_error_desc'] . '"></td>';
                                        break;
                                }
                            }
                            echo '<td>' . date('m/d/y H:i:s', strtotime($trans[$i]['trans_first_post_date'])) . '</td>';
                            
                            $vgh=1;
                            if($trans[$i]['trans_type']==10 || $trans[$i]['trans_type']==2){
                                $vgh=-1;
                            }
                            echo '<td class="hide-xs-screen">$'.number_format(($trans[$i]['trans_net_amount']*$vgh),2).'</td>';

                        if (substr_count($trans[$i]['trans_card_type'], 'Checking') > 0 || substr_count($trans[$i]['trans_card_type'], 'Saving') > 0) {
                            echo '<td class="text-center"><img src="'.asset('img/echeck.png').'"></td>';
                        } elseif (substr_count($trans[$i]['trans_card_type'], 'Visa') > 0) {
                            echo '<td class="text-center"><img src="'.asset('img/visa.png').'"></td>';
                        } elseif (substr_count($trans[$i]['trans_card_type'], 'MasterCard') > 0) {
                            echo '<td class="text-center"><img src="'.asset('img/mastercard.png').'"></td>';
                        } elseif (substr_count($trans[$i]['trans_card_type'], 'Discover') > 0) {
                            echo '<td class="text-center"><img src="'.asset('img/discover.png').'"></td>';
                        } elseif (substr_count($trans[$i]['trans_card_type'], 'American') > 0) {
                            echo '<td class="text-center"><img src="'.asset('img/american.png').'"></td>';
                        } elseif (substr_count($trans[$i]['trans_card_type'], 'CASH') > 0) {
                            echo '<td class="text-center"><img src="'.asset('img/dollar.png').'"></td>';
                        } elseif (substr_count($trans[$i]['trans_card_type'], 'Swipe') > 0) {
                            echo '<td class="text-center"><img src="'.asset('img/swipe.png').'"></td>';
                        } else {
                            echo '<td class="text-center"></td>';
                        }
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
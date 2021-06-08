<form id="editFrequencyForm" method="POST" action="{{ route('saveautopaypaymentfrequency', array('id'=>0)) }}">
{{csrf_field()}}
<div class="row">
    <div class="col-md-6 form-group">
        <label id="xdayLabel">@lang('messages.paymentDate')</label>
        <select name="xday" class="js-select" id="xday" style="width: 100%">
            <?php
            // 1523: load days from settings without loading more days than in the month.
            // e.g. don't load 31 days for September.  
            for($i=$days[0];$i<=min($days[count($days)-1],$daysinmonth); $i++){
                echo '<option value="'.$i.'"';
                if($i==$selday) echo 'selected';
                echo '>';
                // 1523: changed because if current month is Feb and selected month is March.
                echo date('jS',  strtotime(date($curmon.'-'.$i)));
                echo ' '.Lang::get('messages.ofTheMonth');
                echo '</option>';
            }
            ?>
        </select>
    </div>
    <div class="col-md-6 form-group">
        <label id="xfreqLabel">@lang('messages.frequency')</label>
        <select name="xfreq" class="js-select" id="xfreq" style="width: 100%">
            <?php
            foreach ($freq as $key => $value) {
                echo '<option value="'.trim($key).'"';
                if(trim($key)==$selfreq) echo 'selected';
                echo '>'.Lang::get('messages.'.$key).'</option>';
            }

            ?>
        </select>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-group">
        <label id="xstartdateLabel">@lang('messages.start_date')</label>
        <select name="xstartdate" class="js-select" id="xstartdate" style="width: 100%">
            <?php
            for($i=1;$i<count($y1inadvance);$i++){
                echo '<option value="'.$y1inadvance[$i]['value'].'"';
                if($y1inadvance[$i]['value']==$selstart) echo 'selected';
                echo '>';
                echo $y1inadvance[$i]['date'];
                echo '</option>';
            }
            ?>
        </select>
    </div>
    <div class="col-md-6 form-group" id="divenddate">
        <label id="xenddateLabel">@lang('messages.endDate')</label>
        <select name="xenddate" class="js-select" style="width: 100%" id="xenddate" >
            <?php

            for($i=0;$i<count($y5inadvance);$i++){
                echo '<option value="'.$y5inadvance[$i]['value'].'"';
                if($y5inadvance[$i]['value']==$selend) echo 'selected';
                echo '>';
                echo $y5inadvance[$i]['date'];
                echo '</option>';
            }

            ?>
        </select>
    </div>
</div>
</form>
<script>
    var xmonthfixOptions;
    $(document).ready(function() {

        var freq = $('#xfreq').find(":selected").val();
        var enddate = $('#xenddate').find(":selected").val();
        xmonthfixOptions =  $('#xstartdate option').clone();
        if(freq == 'onetime'){
            $('#divenddate').hide();
        }
        
        $('#xstartdate').on('change', function (e) {
            var data = $(this).val();
            var xday = $('#xday').val();
            if(typeof data == 'undefined'){
                data = new Date();
                data = data.getFullYear() + '|' + (data.getMonth() + 1);
            }
            txurl = "{{ route('selectStarDayEdit',array('setting' => ($isdrp == 1) ? 'drp' : 'fix', 'data' => 0, 'startdate' => '1')) }}";
            txurl = txurl.replace("/0", "/"+data);
            txurl = txurl.replace("/1", "/"+xday);
            $("#xstartdate").prop('disabled',true);
            $("#xday").prop('disabled',true);
            ajaxRequest(txurl, null, function (response) {
                if(response.code == 1){
                    $('#xday').html(response.body);
                    $("#xstartdate").prop('disabled',false);
                    $("#xday").prop('disabled',false);
                }
            });
        });

        $('#xfreq').on('change', function (e) {
            var data = $(this).val();
            if (data == 'onetime') {
                $('#divenddate').hide();
                $('#xenddate').find(":selected").val(enddate);
            }
            else {
                $('#divenddate').show();
            }
            
            //restores the orginal month drp options
            $('#xstartdate').html(xmonthfixOptions.clone());
            let frecuency = $(this).val();
            switch (frecuency) {
                case 'quarterly':
                    //removes months
                    $("#xstartdate").find("option[value$='02']").remove();
                    $("#xstartdate").find("option[value$='03']").remove();
                    $("#xstartdate").find("option[value$='05']").remove();
                    $("#xstartdate").find("option[value$='06']").remove();
                    $("#xstartdate").find("option[value$='08']").remove();
                    $("#xstartdate").find("option[value$='09']").remove();
                    $("#xstartdate").find("option[value$='11']").remove();
                    $("#xstartdate").find("option[value$='12']").remove();
                    //selects the new first option
                    $("#xstartdate option:first-child").prop('select', true);
                    $("#xstartdate").trigger('change');
                    break;
                case 'biannually':
                    //removes months
                    $("#xstartdate").find("option[value$='02']").remove();
                    $("#xstartdate").find("option[value$='03']").remove();
                    $("#xstartdate").find("option[value$='04']").remove();
                    $("#xstartdate").find("option[value$='05']").remove();
                    $("#xstartdate").find("option[value$='06']").remove();
                    $("#xstartdate").find("option[value$='08']").remove();
                    $("#xstartdate").find("option[value$='09']").remove();
                    $("#xstartdate").find("option[value$='10']").remove();
                    $("#xstartdate").find("option[value$='11']").remove();
                    $("#xstartdate").find("option[value$='12']").remove();
                    //selects the new first option
                    $("#xstartdate option:first-child").prop('select', true);
                    $("#xstartdate").trigger('change');
                    break;
                case 'triannually':
                    //removes months
                    $("#xstartdate").find("option[value$='02']").remove();
                    $("#xstartdate").find("option[value$='03']").remove();
                    $("#xstartdate").find("option[value$='04']").remove();
                    $("#xstartdate").find("option[value$='06']").remove();
                    $("#xstartdate").find("option[value$='07']").remove();
                    $("#xstartdate").find("option[value$='08']").remove();
                    $("#xstartdate").find("option[value$='10']").remove();
                    $("#xstartdate").find("option[value$='11']").remove();
                    $("#xstartdate").find("option[value$='12']").remove();
                    //selects the new first option
                    $("#xstartdate option:first-child").prop('select', true);
                    $("#xstartdate").trigger('change');
                    break;
            }
        });
        $("#xfreq").trigger('change');
    });
</script>

  
<!-- high charts js-->
<script src="https://code.highcharts.com/highcharts.js"></script>

<script>
  <?php if(is_admin()): ?>
    var incomeData = <?= $income_data; ?>;
    var incomeAxis = <?= $income_axis; ?>;

    Highcharts.chart('adminIncomeChart', {
        chart: {
            type: 'areaspline'
        },
        credits: {
            enabled: false
        },
        title: {
            text: ''
        },
        xAxis: {
            categories: incomeAxis
        },
        yAxis: {
            title: {
                text: ''
            },
            labels: {
                format: '<?php if(settings()->curr_locate == 0){echo html_escape($currency);} ?>{value} <?php if(settings()->curr_locate == 1){echo html_escape($currency);} ?>'
            },
        },
        legend: {
            enabled: true
        },
        plotOptions: {
            series: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    format: '<?php if(settings()->curr_locate == 0){echo html_escape($currency);} ?>{point.y} <?php if(settings()->curr_locate == 1){echo html_escape($currency);} ?>'
                }
            }
        },

        tooltip: {
            headerFormat: '<span class="fs-14">{series.name}</span><br>',
            pointFormat: '<span>{point.name}</span> <b><?php echo html_escape($currency) ?>{point.y}</b><br/>'
        },

        series: [
            {
                name: '<?= trans('income') ?>',
                data: incomeData,
                color: '#2568ef'
            }
        ]
    });


    
  <?php endif; ?>



  <?php if(is_user()): ?>
    var incomeData = <?= $income_data; ?>;
    var incomeAxis = <?= $income_axis; ?>;

    Highcharts.chart('userIncomeChart', {
        chart: {
            type: 'areaspline'
        },
        title: {
            text: ''
        },
        legend: {
            layout: 'vertical',
            align: 'left',
            verticalAlign: 'top',
            x: 150,
            y: 100,
            floating: true,
            borderWidth: 1,
            backgroundColor:
                Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF'
        },
        xAxis: {
            categories: incomeAxis
        },
        yAxis: {
            title: {
                text: ''
            },
            labels: {
                format: '<?php if($this->business->curr_locate == 0){echo html_escape($currency);} ?>{value} <?php if($this->business->curr_locate == 1){echo html_escape($currency);} ?>'
            },
        },
        tooltip: {
            headerFormat: '<span class="fs-14">{series.name}</span><br>',
            pointFormat: '<span>{point.name}</span> <b><?php if($this->business->curr_locate == 0){echo html_escape($currency);} ?>{point.y} <?php if($this->business->curr_locate == 1){echo html_escape($currency);} ?></b><br/>'
        },
        credits: {
            enabled: false
        },
        plotOptions: {
            areaspline: {
                fillOpacity: 0.2,
                dataLabels: {
                    enabled: true,
                    format: '<?php if($this->business->curr_locate == 0){echo html_escape($currency);} ?>{point.y} <?php if($this->business->curr_locate == 1){echo html_escape($currency);} ?>'
                }
            }
        },
        series: [{
            name: '<?php echo trans('income') ?>',
            data: incomeData,
            color: 'rgb(35, 199, 112)'
        }]
    });


  <?php endif; ?>

</script>

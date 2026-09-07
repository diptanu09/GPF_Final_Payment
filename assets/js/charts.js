jQuery.ajax({
  type: "POST",
  url: "ajax/charts/pending.php",
  data: "",
  success: function (returnValue) {
    const { StatusCode, Message, Data } = returnValue;

    if (StatusCode === 200) {
      console.log(Data);
      Highcharts.chart("pending_cases", {
        chart: {
          plotBackgroundColor: null,
          plotBorderWidth: null,
          plotShadow: false,
          type: "pie",
        },
        title: {
          text: "Browser market shares in January, 2018",
        },
        tooltip: {
          pointFormat: "{series.name}: <b>{point.percentage:.1f}%</b>",
        },
        plotOptions: {
          pie: {
            allowPointSelect: true,
            cursor: "pointer",
            dataLabels: {
              enabled: true,
              format: "<b>{point.name}</b>: {point.percentage:.1f} %",
            },
          },
        },
        series: [
          {
            name: "Brands",
            colorByPoint: true,
            data: [
              {
                name: "Other",
                y: 2.61,
              },
            ],
          },
        ],
      });
    } else {
    }
  },
  error: function (a, b, c) {
    console.log(a + " " + b + " " + c);
  },
});

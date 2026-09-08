/******************************************* PRINT AREA *******************************************/
const printPage = (divName) => {
  const printContents = document.getElementById(divName).innerHTML;
  const originalContents = document.body.innerHTML;
  document.body.innerHTML =
    "<div style=font-size:16px;>" + printContents + "</div>";
  window.print();
  document.body.innerHTML = originalContents;
  window.location.reload();
};
/******************************************* PRINT AREA *******************************************/

/******************************************* REDIRECT PAGE *******************************************/

const redirectPage = (pageName) => {
  window.location.href = pageName;
};

/******************************************* REDIRECT PAGE *******************************************/

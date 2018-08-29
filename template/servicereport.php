<?php
if(count(get_included_files()) ==1){
  include("index.php");
  exit;
}
$tmonth  = date("Y-m-01", strtotime($_SESSION['dailyreportdate']));//target month
$tmonthe = date("Y-m-t", strtotime($_SESSION['dailyreportdate']));
$tmonthPresentable = strtoupper(date("F, Y",strtotime($_SESSION['dailyreportdate'])));
$mrObj = new DbTables($con, 'monthreport');
$f = $mrObj->valueLookUp(array('repository_name'), $tmonth, 'report_month');
if(is_null($f)) {
  $content = "No report exists for $tmonthPresentable";
}
else {
  $mrfile = PERFORMANCEREPORTDIR."/".$f[0]['repository_name'];
  $mrdata = getJSONobj($mrfile);
  $mdObj = new month_data($mrdata, $tmonth);

  $prevMonth = getLastMonthArrear($con, $tmonth);

  $payments = getPayments($con, $tmonth, $tmonthe);
  $content = getServiceReport($mdObj, $payments);
}
?>
<div><h5>MONTHLY SERVICE REPORT OF MGA : <?php echo $tmonthPresentable;?></h5></div>
<div class='w3-margin-bottom'>
<?php echo $content;?>
</div>

<?php
function getServiceReport($m, $p){
//  echo "<pre>";print_r($m);echo "</pre>";
  $heads = array('Account','Pax', 'Billded Amount', 'Received Amount',
                          'Due Amount Current','Arrear', 'Arrear Paid', 'Arrear Paid By',
                          'Arrear Carried Over','Total Due','Remarks');
  $accounts = array("1"=>"Spot","2"=>"Bank","3"=>"Corporate");
  $c = "<table class='w3-table-all'>";
  $c .= "<tr><th>".implode("</th><th>",$heads)."</th></tr>";
  for($i=1;$i<4;$i++){
    $aco = $m->md['stream'][$i]['fullarrear'] - $m->md['stream'][$i]['thisarrear'];
    $a = $aco + $p['arrearPayment'][$i]['amount'];
    $c .= "<tr><td>".$accounts[$i]."</td>
               <td>".$m->md['stream'][$i]['pax']."</td>
               <td>".$m->md['stream'][$i]['amount']."</td>
               <td>".$m->md['stream'][$i]['recovered']."</td>
               <td>".$m->md['stream'][$i]['thisarrear']."</td>
               <td>$a</td>
               <td>".$p['arrearPayment'][$i]['amount']."</td>
               <td></td>
               <td>$aco</td>
               <td>".$m->md['stream'][$i]['fullarrear']."</td>
               <td></td>
          </tr>";
  }
  return $c;
}

function getPayments($con, $s, $e){
  $sql = "SELECT invoice.inv_ctype, payment.payment_amount, payment.invoice_month
          FROM `payment`, `invoice`
          WHERE ((`payment_date` BETWEEN '$s' AND '$e') OR (`inv_from_date` BETWEEN '$s' AND '$e')) AND payment.invoice_id=invoice.inv_id";
  $payments['thisMonthPayment'] = array();
  $payments['arrearPayment'] = array();
  for($j=1;$j<4;$j++){
    $payments['thisMonthPayment'][$j]['amount'] = 0;
    $payments['arrearPayment'][$j]['amount'] = 0;
  }
  $dbObj = new DbTables($con, 'payment');
  $r = $dbObj->getSqlResult($sql);
  for($i=0;$i<count($r);$i++){
    if(strtotime($r[$i]['invoice_month'])<strtotime($s))
      $payments['arrearPayment'][$r[$i]['inv_ctype']]['amount'] += $r[$i]['payment_amount'];
    else
      $payments['thisMonthPayment'][$r[$i]['inv_ctype']]['amount'] += $r[$i]['payment_amount'];
  }
  //echo "<pre>";print_r($payments);echo "</pre>";
  return $payments;
}
?>

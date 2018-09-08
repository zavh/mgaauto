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
  $content = getServiceReport($mdObj, $payments, $con);
}
?>
<div><h5>MONTHLY SERVICE REPORT OF MGA : <?php echo $tmonthPresentable;?></h5></div>
<div class='w3-margin-bottom'>
<?php echo $content;?>
</div>

<?php
function getServiceReport($m, $p, $con){
//  echo "<pre>";print_r($m);echo "</pre>";
  $heads = array('Account','Pax', 'Billded Amount', 'Received Amount',
                          'Due Amount Current','Arrear', 'Arrear Paid', 'Arrear Paid By',
                          'Arrear Carried Over','Total Due','Remarks');
  $accounts = array("1"=>"Spot","2"=>"Bank","3"=>"Corporate");
  $c = "<table class='summaryTable w3-hoverable'>";
  $c .= "<tr><th>".implode("</th><th>",$heads)."</th></tr>";
  $arrearFlag = false;
  $monthFlag = false;
  for($i=1;$i<4;$i++){
    $arrearPaymentExpander = "";
    $monthPaymentExpander = "";
    if($i==1) $class='w3-amber';
    else if($i==2) {
      $class='w3-lime';
      $aLink = 'arrearPayingBanks';
      $pLink = 'thismonthPayingBanks';
    }
    else if ($i==3) {
      $class='w3-light-blue';
      $aLink = 'arrearPayingCorps';
      $pLink = 'thismonthPayingCorps';
    }

    if($p['arrearPayment'][$i]['amount']>0){
      $arrearPaymentExpander = getExpander($aLink);
      $arrearFlag = true;
    }
    if($p['thisMonthPayment'][$i]['amount']>0){
      $monthPaymentExpander = getExpander($pLink);
      $monthFlag = true;
    }


    $aco = $m->md['stream'][$i]['fullarrear'] - $m->md['stream'][$i]['thisarrear'];
    $a = $aco + $p['arrearPayment'][$i]['amount'];
    $c .= "<tr class='$class'><td>".$accounts[$i]."</td>
               <td>".$m->md['stream'][$i]['pax']."</td>
               <td>".$m->md['stream'][$i]['amount']."</td>
               <td>$monthPaymentExpander <span style='margin-left:4px'>".$m->md['stream'][$i]['recovered']."</span></td>
               <td>".$m->md['stream'][$i]['thisarrear']."</td>
               <td>$a</td>
               <td>$arrearPaymentExpander <span style='margin-left:4px'>".$p['arrearPayment'][$i]['amount']."</span></td>
               <td></td>
               <td>$aco</td>
               <td>".$m->md['stream'][$i]['fullarrear']."</td>
               <td></td>
          </tr>";
    if($arrearFlag)
      $c .= getArrearPaidOrgs($p['arrearPayment'][$i]['account'], $i, $aLink, $con);

    if($monthFlag)
      $c .= getThismonthPaidOrgs($p['thisMonthPayment'][$i]['account'], $i, $pLink, $con);
  }
  return $c;
}
function getExpander($id){
  $exp = "<span class='w3-black w3-center' style='float:left;vertical-align:middle;'>
    <a href='javascript:void(0)' class='nodec dot' onclick='showPaymentDetails(this)' id='$id'>+</a>
  </span>";
  return $exp;
}
function getArrearPaidOrgs($orgs, $mop, $class, $con){
  $cosmetics = '';
  if($mop == 2) {
    $orgType = 'Bank Name';
    $func = 'getBankName';
  }
  else if($mop == 3) {
    $orgType = 'Coproporate Name';
    $func = 'getCorpName';
  }
  foreach ($orgs as $orgid=>$details){
    $orgName = $func($con, $orgid);
    $orgElid = 'a-'.$mop.'-'.$orgid;
    $thisExpander = getExpander($orgElid);
    $cosmetics .= "<tr class='$class' style='display:none'><td colspan=4></td>";
    $cosmetics .= "<td colspan=2> $orgType : $orgName</td>";
    $cosmetics .= "<td style='padding-left:8px'>$thisExpander<span style='margin-left:4px'>".$details['total_amount']."</span></td>";
    $cosmetics .= "<td colspan=4></td></tr>";
    $cosmetics .= getArrearPaymentDetails($details['payments'], $mop, $orgElid);
  }
  return $cosmetics;
}

function getThismonthPaidOrgs($orgs, $mop, $class, $con){
  $cosmetics = '';
  if($mop == 2) {
    $orgType = 'Bank Name';
    $func = 'getBankName';
  }
  else if($mop == 3) {
    $orgType = 'Coproporate Name';
    $func = 'getCorpName';
  }
  foreach ($orgs as $orgid=>$details){
    $orgName = $func($con, $orgid);
    $orgElid = 't-'.$mop.'-'.$orgid;
    $thisExpander = getExpander($orgElid);
    $cosmetics .= "<tr class='$class' style='display:none'>";
    $cosmetics .= "<td  colspan=3> $orgType : $orgName</td>";
    $cosmetics .= "<td style='padding-left:8px'>$thisExpander<span style='margin-left:4px'>".$details['total_amount']."</span></td>";
    $cosmetics .= "<td colspan=7></td></tr>";
    $cosmetics .= getThismonthPaymentDetails($details['payments'], $mop, $orgElid);
  }
  return $cosmetics;
}

function getBankName($con, $id){
  $bank = new DbTables($con, 'bank');
  $values = $bank->valueLookUp(array('bank_code'), $id, 'bank_id');
  return $values[0]['bank_code'];
}
function getCorpName($con, $id){
  $bank = new DbTables($con, 'corporate');
  $values = $bank->valueLookUp(array('corporate_name'), $id, 'corporate_id');
  return $values[0]['corporate_name'];
}
function getArrearPaymentDetails($p, $mop, $c){
  $pr = "";
  //echo "<pre>";print_r($p);echo "</pre>";
  for($i=0;$i<count($p);$i++){
    $pr .= "<tr class='$c' style='display:none;font-style:italic;'>";
    $pr .= "<td colspan=2 style='text-align:right'>Payment Date: </td><td>".$p[$i]['payment_date']."</td>";
    $pr .= "<td style='text-align:right'>Invoice Ref: </td><td colspan=2>".$p[$i]['invoice_ref']."</td>";
    $pr .= "<td style='padding-left:16px'><span style='margin-left:12px'>".$p[$i]['payment_amount']."</span></td>";
    $pr .= "<td>".$p[$i]['paid_by']."</td>";
    $pr .= "<td></td><td></td>";
    $pr .= "<td>".$p[$i]['payment_comment']."</td>";
    $pr .= "</tr>";
  }
  return $pr;
}
function getThismonthPaymentDetails($p, $mop, $c){
  $pr = "";
  for($i=0;$i<count($p);$i++){
    $pr .= "<tr class='$c' style='display:none;font-style:italic;''>";
    $pr .= "<td colspan=2 style='text-align:right'>Payment Date: </td><td>".$p[$i]['payment_date']."</td>";
    $pr .= "<td style='padding-left:16px'><span style='margin-left:12px'>".$p[$i]['payment_amount']."</span></td>";
    $pr .= "<td style='text-align:right'>Invoice Ref: </td><td colspan=2>".$p[$i]['invoice_ref']."</td>";
    $pr .= "<td>".$p[$i]['paid_by']."</td>";
    $pr .= "<td></td><td></td>";
    $pr .= "<td>".$p[$i]['payment_comment']."</td>";
    $pr .= "</tr>";
  }
  return $pr;
}
function getPayments($con, $s, $e){
  $sql = "SELECT invoice.inv_ctype,
                payment.payment_amount,
                payment.invoice_month,
                payment.invoice_id,
                payment.paid_by,
                payment.payment_comment,
                payment.payment_date,
                invoice.inv_ref,
                invoice.inv_ctype_id
          FROM `payment`, `invoice`
          WHERE ((`payment_date` BETWEEN '$s' AND '$e') OR (`inv_from_date` BETWEEN '$s' AND '$e'))
          AND payment.invoice_id=invoice.inv_id
          ORDER BY payment.payment_date ASC
          ";
  $payments['thisMonthPayment'] = array();
  $payments['arrearPayment'] = array();
  for($j=1;$j<4;$j++){
    $payments['thisMonthPayment'][$j]['amount'] = 0;
    $payments['arrearPayment'][$j]['amount'] = 0;
  }
  $dbObj = new DbTables($con, 'payment');
  $r = $dbObj->getSqlResult($sql);
  //echo "<pre>";print_r($r);echo "</pre>";
  $arrearCount_2 = 0;
  $arrearCount_3 = 0;
  $thismountCount_2 = 0;
  $thismountCount_3 = 0;
  for($i=0;$i<count($r);$i++){
    $ctype = $r[$i]['inv_ctype'];
    $ctype_id = $r[$i]['inv_ctype_id'];
    if(strtotime($r[$i]['invoice_month'])<strtotime($s)){
      $payments['arrearPayment'][$ctype]['amount'] += $r[$i]['payment_amount'];
      if(isset($payments['arrearPayment'][$ctype]['account'][$ctype_id]['total_amount'])){
        $payments['arrearPayment'][$ctype]['account'][$ctype_id]['total_amount'] += $r[$i]['payment_amount'];
      }
      else{
        $payments['arrearPayment'][$ctype]['account'][$ctype_id]['total_amount'] = $r[$i]['payment_amount'];
        ${"arrearCount_".$ctype."_".$ctype_id}=0;
      }
      $payments['arrearPayment'][$ctype]['account'][$ctype_id]['payments'][${"arrearCount_".$ctype."_".$ctype_id}++]=
          array(
            'payment_amount'=>$r[$i]['payment_amount'],
            'invoice_id'=>$r[$i]['invoice_id'],
            'paid_by'=>$r[$i]['paid_by'],
            'payment_comment'=>$r[$i]['payment_comment'],
            'payment_date'=>$r[$i]['payment_date'],
            'invoice_ref'=>$r[$i]['inv_ref']
          );
    }
    else{
      $payments['thisMonthPayment'][$ctype]['amount'] += $r[$i]['payment_amount'];
      if(isset($payments['thisMonthPayment'][$ctype]['account'][$ctype_id]['total_amount'])){
        $payments['thisMonthPayment'][$ctype]['account'][$ctype_id]['total_amount'] += $r[$i]['payment_amount'];
      }
      else{
        $payments['thisMonthPayment'][$ctype]['account'][$ctype_id]['total_amount'] = $r[$i]['payment_amount'];
        ${"thisMonthPayment_".$ctype."_".$ctype_id}=0;
      }
      $payments['thisMonthPayment'][$ctype]['account'][$ctype_id]['payments'][${"thisMonthPayment_".$ctype."_".$ctype_id}++]=
          array(
            'payment_amount'=>$r[$i]['payment_amount'],
            'invoice_id'=>$r[$i]['invoice_id'],
            'paid_by'=>$r[$i]['paid_by'],
            'payment_comment'=>$r[$i]['payment_comment'],
            'payment_date'=>$r[$i]['payment_date'],
            'invoice_ref'=>$r[$i]['inv_ref']
          );
    }
  }
  return $payments;
}
?>

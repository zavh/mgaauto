<?php
$thismonth = date("Y-m");

$payArr = getPayments($con, $thismonth);
$payObj = $payArr['payres'];
$acorps = $payArr['acorps']; //arranged corporate accounts
$bcorps = $payArr['bcorps']; //arranged bank accounts
//echo "<pre>";print_r($summary);echo "</pre>";

$acorps = corpJuxtaposition('corp', $summary, $thismonth, $acorps);
$bcorps = corpJuxtaposition('bank', $summary, $thismonth, $bcorps);

$atab = "<tr class='w3-light-blue'><td colspan='7'>Corporates</td></tr>";
$atab .= corpPresentation($acorps, $payArr);
$btab = "<tr class='w3-lime'><td colspan='7'>Banks</td></tr>";
$btab .= corpPresentation($bcorps, $payArr);

$ctab = "<div style='margin-top:40px;margin-bottom:16px;'>";
$ctab .= "<table style='width:100%;'>";
$ctab .= "<tr class='w3-dark-gray style='text-align:left'>
            <th>Account</th>
            <th>Pax</th>
            <th>Due Current</th>
            <th>Arrear</th>
            <th>Arrear Paid</th>
            <th>Arrear Carried Over</th>
            <th>Total Due</th></tr>";
$ctab .= $atab;
$ctab .= $btab;
$ctab  .= "</table></div>";
echo $ctab;
//echo "<pre>";print_r($summary);echo "</pre>";


function corpJuxtaposition($type, $summary, $thismonth, $accounts){
  foreach($summary as $month=>$details){
    if(!isset($details[$type])) continue;
    foreach ($details[$type] as $corpname => $values) {
      if($month != $thismonth){
        if(isset($values['uninvoiced'])){
          if(isset($accounts[$corpname]['arrear']['total']))
            $accounts[$corpname]['arrear']['total'] += $values['uninvoiced']['amount'];
          else
            $accounts[$corpname]['arrear']['total'] = $values['uninvoiced']['amount'];
          $accounts[$corpname]['arrear']['uninvoiced'][$month]['amount'] = $values['uninvoiced']['amount'];
        }
        if(isset($values['invoiced'])){
          foreach ($values['invoiced']['invoice'] as $inv_id=>$inv_detail){
            if(!isset($accounts[$corpname]['arrear']['invoiced'][$month]['invoice'][$inv_id])){
              if(isset($accounts[$corpname]['arrear']['invoiced'][$month]['amount']))
                $accounts[$corpname]['arrear']['invoiced'][$month]['amount'] += $values['invoiced']['arrear'];
              else
                $accounts[$corpname]['arrear']['invoiced'][$month]['amount'] = $values['invoiced']['arrear'];
              if(isset($accounts[$corpname]['arrear']['total']))
                $accounts[$corpname]['arrear']['total'] += $values['invoiced']['arrear'];
              else
                $accounts[$corpname]['arrear']['total'] = $values['invoiced']['arrear'];
            }
            else continue;
            $accounts[$corpname]['arrear']['invoiced'][$month]['invoice'][$inv_id] = $values['invoiced']['invoice'][$inv_id];
          }
        }
      }
      else {
        if(isset($values['uninvoiced'])){
          $accounts[$corpname]['thismonth']['dueamount'] = $values['uninvoiced']['amount'];
          $accounts[$corpname]['thismonth']['pax'] = $values['uninvoiced']['pax'];
        }
      }
    }
  }
  return $accounts;
}

function corpPresentation($accounts, $payArr){
  $ctab ='';
  foreach ($accounts as $corpname => $value) {
    $corpid = str_replace(" ","_",$corpname);
    $thispax = '-'; $thisdue = '0';
    $uninvoiced['presentation'] = '';
    $invoiced['presentation'] = '';
    $rows = 1;
    if(isset($value['arrear']['uninvoiced'])){
      $uninvoiced = getUninvoiced($value['arrear']['uninvoiced'], $corpid);
      $rows += $uninvoiced['row'];
    }
    if(isset($value['arrear']['invoiced'])){
      $invoiced = getInvoiced($value['arrear']['invoiced'], $payArr, $corpid);
      $rows += $invoiced['row'];
    }
    if(isset($value['thismonth'])){
      $thispax = $value['thismonth']['pax'];
      $thisdue = $value['thismonth']['dueamount'];
    }
    $totalarrear = 0 ;
    if(isset($value['arrear'])){
      $totalarrear = $value['arrear']['total'];
    }
    $payment = 0 ;
    if(isset($value['arrear']['payment'])){
      $payment = $value['arrear']['payment'];
    }
    $totaldues = $thisdue + $totalarrear - $payment  ;
    $arrearcarried = $totalarrear - $payment;

    $detailview = "onclick=\"accountsView($rows, this)\"";

    $ctab .= "<tr class='w3-gray' id='$corpid' $detailview>
                <td id='td-$corpid'>".$corpname."</td>
                <td>$thispax</td>
                <td>$thisdue</td>
                <td>$totalarrear</td>
                <td>$payment</td>
                <td>$arrearcarried</td>
                <td>$totaldues</td>
              </tr>";
    $ctab .= $invoiced['presentation'];
    $ctab .= $uninvoiced['presentation'];

  }
  return $ctab;
}

function getUninvoiced($arr, $class){
  $presentation['total'] = 0;
  $presentation['presentation'] = '';
  $presentation['row'] = 0;
  foreach ($arr as $month=>$value){
    $presentation['total'] += $value['amount'];
    $presentation['presentation'] .= "<tr class='w3-pale-red $class' style='display:none'>
      <td colspan=2 style='text-align:right'>Month: ".date("M, Y",strtotime($month))."</td>
      <td><span style='margin-left:8px'>".$value['amount']."</span></td>
      <td>[yet to be invoiced]</td>
      <td><span style='margin-left:8px'>".$value['amount']."</span></td>
      <td><span style='margin-left:8px'>".$value['amount']."</span></td>
      </tr>";
      $presentation['row']++;
  }
  return $presentation;
}
function getInvoiced($arr, $payArr, $class){
  $presentation['presentation'] = '';
  $presentation['row'] = 0;
  foreach ($arr as $month=>$value){
    $montharrear = 0; $monthpayment = 0; $monthrow = 1;$paymentdetails = '';$monthdetails = '';
    foreach ($value['invoice'] as $invid => $invval) {
      // *********** Has payments ??? Payment processing starts
      if(isset($invval['payment_poniter'])){
        $arrear = $invval['payment'] + $invval['arrear'];
        $montharrear += $arrear;
        $monthpayment += $invval['payment'];
        $payments = explode("|", $invval['payment_poniter']);
        $numpays = count($payments);
        $monthrow += $numpays;
        if($numpays>1){
          for($i=0;$i<$numpays;$i++){
            $payment_amount = $payArr['payres'][$payments[$i]]['payment_amount'];
            $payment_details  = "<table style='border-collapse:collapse;width:100%'>
                                  <tr>
                                    <td style='width:33%'>Paid By:".$payArr['payres'][$payments[$i]]['paid_by']."</td>
                                    <td style='width:33%'>Comment:".$payArr['payres'][$payments[$i]]['payment_comment']."</td>
                                    <td style='width:33%'>Paid On:".$payArr['payres'][$payments[$i]]['payment_date']."</td>
                                  </tr>
                                </table>";
            if($i==0){
              $paymentdetails .= "<tr class='w3-pale-green $class' style='display:none'>
                                    <td rowspan=$numpays><span style='margin-left:16px'>$arrear</span></td>
                                    <td><span style='margin-left:16px'>$payment_amount</span></td>
                                    <td>$payment_details</td>
                                    <td rowspan=$numpays>".$invval['ref']."</td>
                                  </tr>";
            }
            else {
              $paymentdetails .= "<tr class='w3-pale-green $class' style='display:none'>
                                    <td><span style='margin-left:16px'>$payment_amount</span></td>
                                    <td>$payment_details</td>
                                  </tr>";
            }
          }
        }
        else{
          $payment_amount = $payArr['payres'][$payments[0]]['payment_amount'];
          $payment_details  = "<table style='border-collapse:collapse;width:100%'>
                                <tr>
                                  <td style='width:33%'>Paid By:".$payArr['payres'][$payments[0]]['paid_by']."</td>
                                  <td style='width:33%'>Comment:".$payArr['payres'][$payments[0]]['payment_comment']."</td>
                                  <td style='width:33%'>Paid On:".$payArr['payres'][$payments[0]]['payment_date']."</td>
                                </tr>
                              </table>";
          $paymentdetails .= "<tr class='w3-pale-green $class' style='display:none'>
                                <td><span style='margin-left:16px'>$arrear</span></td>
                                <td><span style='margin-left:16px'>$payment_amount</span></td>
                                <td>$payment_details</td>
                                <td>".$invval['ref']."</td>
                              </tr>";
        }
      } // *********** Payment processing ends
      else {
        $paymentdetails .= "<tr class='w3-pale-yellow $class' style='display:none'>
                              <td ><span style='margin-left:16px'>".$invval['arrear']."</span></td>
                              <td>-</td>
                              <td>-</td>
                              <td>".$invval['ref']."</td>
                            </tr>";
        $montharrear += $invval['arrear'];
        $monthrow ++; //single row for invoice, so month row increases
      }
    } // ########## Invoice processing ends
    $monthcarried = $montharrear - $monthpayment;
    $monthdetails .= "<tr class='w3-yellow $class' style='display:none'>
                          <td colspan=2 rowspan=$monthrow style='text-align:right'>Month: ".date("M, Y",strtotime($month))."</td>
                          <td><span style='margin-left:8px'>$montharrear</span></td>
                          <td><span style='margin-left:8px'>$monthpayment</span></td>
                          <td><span style='margin-left:8px'>$monthcarried</span></td>
                          <td><span style='margin-left:8px'>$monthcarried</span></td>
                        </tr>";
    $presentation['presentation'] .= $monthdetails . $paymentdetails;
    $presentation['row'] += $monthrow;
  }
  return $presentation;
}

function getPayments($con, $month){
  $start = date("Y-m-01", strtotime($month));
  $end = date("Y-m-t", strtotime($month));

  $sql = "SELECT  inv_id,
                  inv_ref,
                  inv_ctype,
                  inv_from_date,
                  inv_ctype_id,
                  inv_amount,
                  inv_arrear,
                  payment_id,
                  payment_date,
                  payment_amount,
                  payment_comment,
                  paid_by
          FROM `invoice`, `payment`
          WHERE payment.payment_date BETWEEN '$start' AND '$end'
          AND invoice.inv_id = payment.invoice_id";

  $payObj = new DbTables($con, 'invoice');
  $payRes = $payObj->getSqlResult($sql);
  if(count($payRes)>0){
    $payArr['payres'] = $payRes;
    for($i=0;$i<count($payRes);$i++){
      $mop = $payRes[$i]['inv_ctype'];
      $month = date("Y-m",strtotime($payRes[$i]['inv_from_date']));
      if($mop == 2){
        $corpname = getBankName($con, $payRes[$i]['inv_ctype_id']);
        $arrname = 'bcorps';
      }

      else if($mop == 3){
        $corpname = getCorpName($con, $payRes[$i]['inv_ctype_id']);
        $arrname = 'acorps';
      }
      if(isset($payArr[$arrname][$corpname]['arrear']['total'])){
        if(!isset($payArr[$arrname][$corpname]['arrear']['invoiced'][$month]['invoice'][$payRes[$i]['inv_id']])){
          $payArr[$arrname][$corpname]['arrear']['total'] += $payRes[$i]['inv_amount'];
          $payArr[$arrname][$corpname]['arrear']['payment'] += $payRes[$i]['payment_amount'];
          $payArr[$arrname][$corpname]['arrear']['invoiced'][$month]['invoice'][$payRes[$i]['inv_id']]['payment'] = $payRes[$i]['payment_amount'];
          $payArr[$arrname][$corpname]['arrear']['invoiced'][$month]['invoice'][$payRes[$i]['inv_id']]['payment_poniter'] = $i;
        }
        else {
          $payArr[$arrname][$corpname]['arrear']['invoiced'][$month]['invoice'][$payRes[$i]['inv_id']]['payment'] += $payRes[$i]['payment_amount'];
          $payArr[$arrname][$corpname]['arrear']['invoiced'][$month]['invoice'][$payRes[$i]['inv_id']]['payment_poniter'] .= "|".$i;
          $payArr[$arrname][$corpname]['arrear']['payment'] += $payRes[$i]['payment_amount'];
        }
      }
      else {
        $payArr[$arrname][$corpname]['arrear']['total'] = $payRes[$i]['inv_amount'];
        $payArr[$arrname][$corpname]['arrear']['payment'] = $payRes[$i]['payment_amount'];
        $payArr[$arrname][$corpname]['arrear']['invoiced'][$month]['invoice'][$payRes[$i]['inv_id']]['payment'] = $payRes[$i]['payment_amount'];
        $payArr[$arrname][$corpname]['arrear']['invoiced'][$month]['invoice'][$payRes[$i]['inv_id']]['payment_poniter'] = $i;
      }
      $payArr[$arrname][$corpname]['arrear']['invoiced'][$month]['invoice'][$payRes[$i]['inv_id']]['amount'] = $payRes[$i]['inv_amount'];
      $payArr[$arrname][$corpname]['arrear']['invoiced'][$month]['invoice'][$payRes[$i]['inv_id']]['arrear'] = $payRes[$i]['inv_arrear'];
      $payArr[$arrname][$corpname]['arrear']['invoiced'][$month]['invoice'][$payRes[$i]['inv_id']]['ref'] = $payRes[$i]['inv_ref'];
      $payArr[$arrname][$corpname]['arrear']['invoiced'][$month]['invoice'][$payRes[$i]['inv_id']]['id'] = $payRes[$i]['inv_id'];
    }
    return $payArr;
  }
  else return null;

}
?>

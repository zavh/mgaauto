<?php
class DashDat{
	public $recovered = array();
  public $unInvoiced = array();
  public $invoiced = array();
  public $rd = array();
  public $invoices = array(); // To determine the number of invoices
  public $clients = array();
  public $invoicePaid = array();//TO determine invoices already paid
  public $uinreccount = 0; // To determine how many uninvoiced records
  public $paxArr = array();
  public $recArr = array();
  public $dailyDat = array();
  public $month;

  function __construct($rd, $month) {
    $dailyDat = $this->getMonthArr($month);
    $recovered = array();
    $unInvoiced = array();
    $invoiced = array();
    $invoices = array();
    $invoicePaid = array();
    $uinreccount = 0;
    $pasxArr = array();
    $recArr = array();
    $clients[1] = array();
		$payment = array();

    for($i=1;$i<4;$i++){
      $paxArr[$i] = 0;
      $recArr[$i]=0;
      $invoices[$i] = array();
    }

    //$this->rd = $rd;
    $recovered = $this->init($recovered);
    $unInvoiced = $this->init($unInvoiced);
    $invoiced = $this->init($invoiced);

    for($i=0;$i<count($rd);$i++){
      $mop = $rd[$i]['mode_of_payment'];
      $dailyDat = $this->popupdateMonthArr($rd[$i],$dailyDat);
      $paxArr[$mop]+= $rd[$i]['no_of_passengers'];
      $recArr[$mop]++;
      if($mop==1){
        $recovered[$mop] = $this->juxtaposition($recovered[$mop], $rd[$i]);
      }
      else {
        if($mop == 2){
          if(isset($clients[$mop][$rd[$i]['bank_id']]))
            $clients[$mop][$rd[$i]['bank_id']]++;
          else $clients[$mop][$rd[$i]['bank_id']] = 1;
        }
        else if($mop == 3){
          if(isset($clients[$mop][$rd[$i]['corporate_id']]))
            $clients[$mop][$rd[$i]['corporate_id']]++;
          else $clients[$mop][$rd[$i]['corporate_id']] = 1;
        }
        if($rd[$i]['invoice'] == 0){
          $unInvoiced[$mop] = $this->juxtaposition($unInvoiced[$mop], $rd[$i]);
          $uinreccount++;
        }
        if($rd[$i]['invoice'] > 0){
					$invoiced[$mop] = $this->juxtaposition($invoiced[$mop], $rd[$i]);
          if(isset($invoices[$mop][$rd[$i]['invoice']]))
            $invoices[$mop][$rd[$i]['invoice']]++;
          else $invoices[$mop][$rd[$i]['invoice']] = 1;

					//check if partially paid. This will be added to Recovered
					if(is_null($rd[$i]['inv_paid_on']) && ($rd[$i]['inv_amount'] != $rd[$i]['inv_arrear'])){
						if(!isset($payment[$mop][$rd[$i]['invoice']])){
							$payment[$mop][$rd[$i]['invoice']] = $rd[$i]['inv_amount'] - $rd[$i]['inv_arrear'];
							$recovered[$mop]['amount'] += $payment[$mop][$rd[$i]['invoice']];
						}
					}
          else if($rd[$i]['inv_arrear'] == 0){
            $recovered[$mop]= $this->juxtaposition($recovered[$mop], $rd[$i]);
            $invoicePaid[$mop][$rd[$i]['invoice']] = $rd[$i]['inv_ref'];
          }
        }
      }
    }

    $this->recovered = $recovered;
    $this->unInvoiced = $unInvoiced;
    $this->invoiced = $invoiced;
    $this->invoices = $invoices;
    $this->invoicePaid = $invoicePaid;
    $this->uinreccount = $uinreccount;
    $this->paxArr = $paxArr;
    $this->recArr = $recArr;
    $this->clients = $clients;
    $this->dailyDat = $dailyDat;
    $this->month = $month;
  }
	public function getDonut(){
		$recoveredRecords = $this->recovered[1]['count'];
		$unInvoicedRecords = 0;
		$invoicedRecords = 0;
		for($i=2;$i<4;$i++){
			$recoveredRecords += $this->recovered[$i]['count'];
			$unInvoicedRecords += $this->unInvoiced[$i]['count'];
			$invoicedRecords += ($this->invoiced[$i]['count'] - $this->recovered[$i]['count']);
		}
		return $invoicedRecords.",".$unInvoicedRecords.",".$recoveredRecords;
	}
	public function getPie(){
		$cashAmount = $this->recovered[1]['amount'];
		$paidAmount = 0;
		$pendingAmount = 0;
		for($i=2;$i<4;$i++){
			$paidAmount += $this->recovered[$i]['amount'];
			$pendingAmount += ($this->unInvoiced[$i]['amount'] + $this->invoiced[$i]['amount']);
		}
		$pendingAmount -= $paidAmount;
		return $cashAmount.",".$paidAmount.",".$pendingAmount;
	}
  private function init($arr){
    for($i=1;$i<4;$i++){
      $arr[$i]['amount'] = 0;
      $arr[$i]['count'] = 0;
    }
    return $arr;
  }

  private function juxtaposition($stream, $r){
  		$stream['amount'] += $r['amount'];
  		//$stream['record'][$stream['count']] = $r;
  		$stream['count']++;
  		return $stream;
  	}
  private function getMonthArr($month){
    $end = intval(date("t",strtotime($month)));
    $dayArr = array();
    for($i=1;$i<=$end;$i++){
      for($j=1;$j<4;$j++){
        $dayArr[date("Y-m-".sprintf('%02d', $i),strtotime($month))][$j]['amount'] = 0;
        $dayArr[date("Y-m-".sprintf('%02d', $i),strtotime($month))][$j]['pax'] = 0;
        $dayArr[date("Y-m-".sprintf('%02d', $i),strtotime($month))][$j]['request'] = 0;
      }
    }
    return $dayArr;
  }

  private function popupdateMonthArr($r, $dayArr){
    $dayArr[$r['flight_date']][$r['mode_of_payment']]['amount'] += $r['amount'];
    $dayArr[$r['flight_date']][$r['mode_of_payment']]['pax'] += $r['no_of_passengers'];
    $dayArr[$r['flight_date']][$r['mode_of_payment']]['request']++;
    return $dayArr;
  }

  public function getChartLabel(){
    $month = $this->month;
    $end = intval(date("t",strtotime($month)));
    $labels = '';
    $comma = ",";
    for($i=1;$i<=$end;$i++){
      if($i==$end) $comma = "";
      $labels .= "\"".sprintf('%02d', $i)."\"$comma";
    }
    return $labels;
  }
  public function getChartDat(){
    $dd = $this->dailyDat;
    $datArr = array();
    for($i=1;$i<4;$i++){
      $datArr[$i] = array('amString'=>'','paxString'=>'','reqString'=>'');
    }
    foreach ($dd as $ddate => $valArr) {
      for($i=1;$i<4;$i++){
        $datArr[$i]['amString'] .= ($valArr[$i]['amount']).",";
        $datArr[$i]['paxString'] .= $valArr[$i]['pax'].",";
        $datArr[$i]['reqString'] .= $valArr[$i]['request'].",";
      }
    }
    return $datArr;
  }
}
?>

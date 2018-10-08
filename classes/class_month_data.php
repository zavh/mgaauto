<?php
class month_data{
  public $md; //All post data
  public $filename; //To Filename where new month data will be saved
  public $client_id;
  public $prev_arrear;

  function __construct($md) {
    $this->md = $md;
  }
  public function setRepository($mrFileName){
    $this->filename = $mrFileName;
  }
  public function setPrevArrear($prev_arrear){
    $this->prev_arrear = $prev_arrear;
  }
  public function setMonth($month){
    $this->md['month'] = $month;
  }
  public function setArrear(){
    for($i=1;$i<4;$i++){
      $this->md['stream'][$i]['thisarrear'] = $this->md['stream'][$i]['amount'] - $this->md['stream'][$i]['recovered'];
      $this->md['stream'][$i]['fullarrear'] = $this->md['stream'][$i]['thisarrear'] + $this->prev_arrear[$i];
    }
  }
  public function getCurrentDues(){
    return $this->md['pending_payment'];
  }
  public function getArrearArr(){
    $a = array();
    for($i=1;$i<4;$i++){
      $a[$i] = $this->md['stream'][$i]['fullarrear'];
    }
    return $a;
  }
  public function add($new){
    $mop = $new['payment']; // mop 1: Spot, mop 2: Bank, mop 3: Corporate/Due
    $this->md['total'] += $new['amount'];
    $this->md['total_pax']+=$new['pnumber'];
    $this->md['total_rec']++;
    $this->md['stream'][$mop]['amount'] += $new['amount'];
    $this->md['stream'][$mop]['pax'] += $new['pnumber'];
    $this->md['stream'][$mop]['request']++;
    $d = $this->getDayOfMonth($new['dtravel']);
    foreach($this->md['streamMonthDat'][$mop] as $key=>$value){
      if($key=='amString')
        $addVal = $new['amount'];
      else if($key=='paxString')
        $addVal = $new['pnumber'];
      else if($key=='reqString')
        $addVal = 1;
      $this->md['streamMonthDat'][$mop][$key] = $this->getStreamMonthDat($value, $d, $addVal);
    }
    if($mop == 1){                                                                  #For donut 0 - Invoiced, 1 - Uninvoiced, 2- Paid [No of records]
      $this->md['cash'] += $new['amount'];                                          #For pie 0 - Cash, 1 - Paid, 2- Pending [Amount]
      $this->md['stream'][$mop]['recovered'] += $new['amount'];
      $this->md['donut'] = $this->getFoodUpdate($this->md['donut'],2,1);            #increasing by 1 here since spot = paid and one paid record has been increased
      $this->md['pie'] = $this->getFoodUpdate($this->md['pie'],0,$new['amount']);   #increasing by amount, since Cash
    }
    else {
      $this->md['invoice'] += $new['amount'];
      $this->md['pending_payment'] += $new['amount'];
      $this->md['uninv_records']++;
      $this->md['stream'][$mop]['uninv_records']++;
      $this->md['donut'] = $this->getFoodUpdate($this->md['donut'],1,1);
      $this->md['pie'] = $this->getFoodUpdate($this->md['pie'],2,$new['amount']);
      if(isset($this->md['clients'][$mop][$this->client_id]))
        $this->md['clients'][$mop][$this->client_id]++;
      else $this->md['clients'][$mop][$this->client_id] = 1;
      $this->md['stream'][$mop]['noclients'] = count($this->md['clients'][$mop]);
    }
    $this->setWidgetData();
    $this->setArrear();
    $this->writeMonthlyData($this->md);
  }

  public function delete($str){
    $reqObj = json_decode($str, true);
    //echo "<pre>";print_r($this->md);echo "</pre>";

    $mop = $reqObj['mode_of_payment'];
    $this->md['total'] -= $reqObj['amount'];
    $this->md['total_pax'] -= $reqObj['no_of_passengers'];
    $this->md['total_rec']--;
    $this->md['stream'][$mop]['amount'] -= $reqObj['amount'];
    $this->md['stream'][$mop]['pax'] -= $reqObj['no_of_passengers'];
    $this->md['stream'][$mop]['request']--;
    //Changing Big Lime bar chart of monthly data
    $d = $this->getDayOfMonth($reqObj['flight_date']); //determining which element of the month array needs to be changed
    foreach($this->md['streamMonthDat'][$mop] as $key=>$value){
      if($key=='amString')
        $addVal = $reqObj['amount']*(-1); //Setting negative
      else if($key=='paxString')
        $addVal = $reqObj['no_of_passengers']*(-1); //Setting negative
      else if($key=='reqString')
        $addVal = -1; //Setting negative
      $this->md['streamMonthDat'][$mop][$key] = $this->getStreamMonthDat($value, $d, $addVal);
    }
    if($mop == 1){
      $this->md['cash'] -= $reqObj['amount'];
      $this->md['stream'][$mop]['recovered'] -= $reqObj['amount'];
      $this->md['donut'] = $this->getFoodUpdate($this->md['donut'],2,-1);
      $this->md['pie'] = $this->getFoodUpdate($this->md['pie'],0,$reqObj['amount']*(-1));
    }
    else {
      if($mop == 2) $this->setClientId($reqObj['bank_id']);
      else if($mop == 3) $this->setClientId($reqObj['corporate_id']);
      $this->md['invoice'] -= $reqObj['amount'];
      $this->md['pending_payment'] -= $reqObj['amount'];
      $this->md['uninv_records']--;
      $this->md['stream'][$mop]['uninv_records']--;
      $this->md['donut'] = $this->getFoodUpdate($this->md['donut'],1,-1);
      $this->md['pie'] = $this->getFoodUpdate($this->md['pie'],2,$reqObj['amount']*(-1));
      $this->md['clients'][$mop][$this->client_id]--;
      if($this->md['clients'][$mop][$this->client_id] == 0){
        unset($this->md['clients'][$mop][$this->client_id]);
        $this->md['stream'][$mop]['noclients'] = count($this->md['clients'][$mop]);
      }
    }
    $this->setWidgetData(); //Finally Recalculate the right most widgets
    $this->setArrear();
    $this->writeMonthlyData($this->md); //Saving monthly data
    //echo "<pre>";print_r($this->md);echo "</pre>";
  }
  //For changing data for the big monthly bar chart of daily AMOUNT, PAX and REQUEST data
  private function getStreamMonthDat($string, $ordinal, $addVal){
    $arr = explode(',',$string);
    $arr[$ordinal] = intval($arr[$ordinal])+$addVal;
    return implode(',',$arr);
  }
  private function getDayOfMonth($recordDate){
    $day = intval(date("j",strtotime($recordDate)));
    return ($day-1);
  }
  public function writeMonthlyData($jsonDat){
    $formattedData = json_encode($jsonDat);
  	$handle = fopen($this->filename,'w+');
  	fwrite($handle,$formattedData);
  	fclose($handle);
  }
  private function getFoodUpdate($fooddat, $index, $value){
    $dat = explode(',',$fooddat);
    $dat[$index] = intval($dat[$index])+$value;
    return implode(',',$dat);
  }
  private function setWidgetData(){
    for($i=1;$i<4;$i++){
      if($this->md['total'] == 0){
        $this->md['stream'][$i]['revgen'] = 0;
        $this->md['stream'][$i]['reqgen'] = 0;
        $this->md['stream'][$i]['paxgen'] = 0;
      }
      else {
        $this->md['stream'][$i]['revgen'] = ($this->md['stream'][$i]['amount']/$this->md['total'])*100;
        $this->md['stream'][$i]['reqgen'] = ($this->md['stream'][$i]['request']/$this->md['total_rec'])*100;
        $this->md['stream'][$i]['paxgen'] = ($this->md['stream'][$i]['pax']/$this->md['total_pax']*100);
      }
    }
  }
  public function setClientId($client_id){
    $this->client_id = $client_id;
  }
  public function setChartLabel($month){
    $end = intval(date("t",strtotime($month)));
    $labels = '';
    $comma = ",";
    for($i=1;$i<=$end;$i++){
      if($i==$end) $comma = "";
      $labels .= "\"".sprintf('%02d', $i)."\"$comma";
    }
    $this->md['chartlabel'] = $labels;
  }
  public function invoiceAdd($rc, $mop){
    $this->md['invoice_raised']++;
    $this->md['invoice_unpaid']++;
    $this->md['uninv_records'] -= $rc;
    $this->md['stream'][$mop]['raised_invoice']++;
    $this->md['stream'][$mop]['uninvcount'] -= $rc;
    $this->md['stream'][$mop]['uninv_records'] -= $rc;
    $this->md['donut'] = $this->getFoodUpdate($this->md['donut'],0,$rc);
    $this->md['donut'] = $this->getFoodUpdate($this->md['donut'],1,(-1)*$rc);
    $this->writeMonthlyData($this->md);
  }
  public function invoiceDelete($rc, $mop){
    $this->md['invoice_raised']--;
    $this->md['invoice_unpaid']--;
    $this->md['uninv_records'] += $rc;
    $this->md['stream'][$mop]['raised_invoice']--;
    $this->md['stream'][$mop]['uninvcount'] += $rc;
    $this->md['stream'][$mop]['uninv_records'] += $rc;
    $this->md['donut'] = $this->getFoodUpdate($this->md['donut'],0,(-1)*$rc);
    $this->md['donut'] = $this->getFoodUpdate($this->md['donut'],1,$rc);
    $this->writeMonthlyData($this->md);
  }
  public function addPayment($amount, $fpf, $mop, $rc){
    $this->md['recovered'] += $amount;
    $this->md['pending_payment'] -= $amount;
    $this->md['invoice_paid'] += $fpf;
    $this->md['stream'][$mop]['recovered'] += $amount;
    $this->md['stream'][$mop]['invoice_paid'] += $fpf;
    $this->md['invoice_unpaid'] -= $fpf;
    $this->md['stream'][$mop]['thisarrear'] -= $amount;
    $this->md['stream'][$mop]['fullarrear'] -= $amount;
    $this->md['donut'] = $this->getFoodUpdate($this->md['donut'],2,$rc);
    $this->md['donut'] = $this->getFoodUpdate($this->md['donut'],0,(-1)*$rc);
    $this->md['pie'] = $this->getFoodUpdate($this->md['pie'],1,$amount);
    $this->md['pie'] = $this->getFoodUpdate($this->md['pie'],2,$amount*(-1));
    $this->writeMonthlyData($this->md);
  }
  public function removePayment($amount, $fpf, $mop, $rc){
    $this->md['recovered'] -= $amount;
    $this->md['pending_payment'] += $amount;
    $this->md['invoice_paid'] -= $fpf;
    $this->md['stream'][$mop]['recovered'] -= $amount;
    $this->md['stream'][$mop]['invoice_paid'] -= $fpf;
    $this->md['invoice_unpaid'] += $fpf;
    $this->md['stream'][$mop]['thisarrear'] += $amount;
    $this->md['stream'][$mop]['fullarrear'] += $amount;
    $this->md['donut'] = $this->getFoodUpdate($this->md['donut'],0,$rc);
    $this->md['donut'] = $this->getFoodUpdate($this->md['donut'],2,(-1)*$rc);
    $this->md['pie'] = $this->getFoodUpdate($this->md['pie'],2,$amount);
    $this->md['pie'] = $this->getFoodUpdate($this->md['pie'],1,$amount*(-1));
    $this->writeMonthlyData($this->md);
  }
}
?>

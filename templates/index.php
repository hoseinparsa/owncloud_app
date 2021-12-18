<?php
	script('recognition', 'script');
?>

<div class="contanir" style="width: 100%; min-height: 100vh; display: flex; justify-content: center; align-items: center; background-color: #cad3eb; overflow: auto; margin:0; box-sizing: border-box; color: black;">
    <table></table>
<table style="width:50%;border-spacing: 0 10px;" >
  <tr>
    <td style="text-align: center;"><label>Token</label></td>
    <td><input type="text" id="MANA_Token" style="width: 60%;"  value="<?php echo $_['Token']; ?>"></td>
  </tr>
  <tr>
    <td style="text-align: center;"><label>Face Detection Accuracy(precent)</label></td>
    <td><input type="text" id="FaceDetectionAccuracy" style="width: 60%;" class="input" value="<?php echo $_['FaceDetectionAccuracy']; ?>"></td>
  </tr>
  <tr>
    <td style="text-align: center;"><label>Server Api</label></td>
    <td><input type="text" id="ServerApi" name="ServerApi" style="width: 60%;" class="input" value="<?php echo $_['ServerApi']; ?>"></td>
  </tr>
  <tr>
    <td style="text-align: center;"><label>Object Detection</label></td>
    <td><input type="checkbox" class="input" name="object" <?php if($_['Object'] == 'True'){echo 'checked';} ?> id="object"></td>
  </tr>
  <tr>
    <td style="text-align: center;"><label>Multi Faces Status</label></td>
    <td><input type="checkbox" id="MultiFacesStatus"   class="input" <?php if($_['MultiFacesStatus'] == 'True'){echo 'checked';} ?> >
  </tr>
  <tr>
    <td style="text-align: center;"><label>Allow Growth Status</label></td>
    <td><input type="checkbox" id="GrowthStatus"  class="input" <?php if($_['GrowthStatus'] == 'True'){echo 'checked';} ?>></td>
  </tr>

  <tr>
    <td></td>
    <td><button class="events--button button btn primary ng-scope" style="margin: auto;width: 65%;" id="save" name="save">Save</button></td>
  </tr>

</tabel>
</div>

(function (OC, window, $, undefined) {
'use strict';

	$(document).ready(function () {
		var BaseUrl = OC.generateUrl('apps/recognition')
		$( "#save" ).click(function() {
			var Token = document.getElementById("MANA_Token").value;
			var ServerApi = document.getElementById("ServerApi").value;
			var FaceDetectionAccuracy = document.getElementById("FaceDetectionAccuracy").value;
			var Object = document.getElementById("object").checked;
			var MultiFacesStatus = document.getElementById("MultiFacesStatus").checked;
			var GrowthStatus = document.getElementById("GrowthStatus").checked;
		$.ajax({
                        url: BaseUrl + '/api/v1/SaveSettings',
                	method: 'POST',
                	contentType: 'application/json',
                        data: JSON.stringify({'MANA_Token': Token,'Object':Object,
						'ServerApi':ServerApi,'Accuracy':FaceDetectionAccuracy,
						'MultiFaces':MultiFacesStatus,'GrowthStatus':GrowthStatus}),
                	}).done(function (response) {
                        	//return response
				if(response.Code === 200){
                                	OCdialogs.info(response.msg,'Success Saved')
				}
                	}).fail(function (response, code) {
                        	// handle failure
                        	OCdialogs.info('Faild To Save Settings','Error')
                	});

		});

	});

})(OC, window, jQuery);

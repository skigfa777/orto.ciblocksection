function copy_section(id) {
  var successWindow = new BX.PopupWindow("success", null, {
      content: BX('ajax-server-ip'),
      // content: 'Раздел #' + id + ' скопирован!',
      closeIcon: {right: "20px", top: "10px"},
      overlay: {backgroundColor: '#333', opacity: '50' },
      draggable: {restrict: false},
      zIndex: 0,
      offsetLeft: 0,
      offsetTop: 0,
      draggable: {restrict: false},
      buttons: [
          new BX.PopupWindowButton({
              text: "Закрыть",
              events: {click: function() {
                this.popupWindow.close();
                location.reload();
              }}
          })
      ]
  }); 

  BX.ajax({   
    url: '/bitrix/admin/orto.ciblocksection/ajax.php',
    data: {
      id: id
    },
    method: 'POST',
    dataType: 'json',
    timeout: 60,
    async: true,
    processData: true,
    scriptsRunFirst: true,
    emulateOnload: true,
    start: true,
    cache: false,
    onsuccess: function(data) {
      successWindow.setContent('<div class="main-grid-confirm-content" style="text-align:left;">' + data.message + '</div>');
      successWindow.show(); 
    },
    onfailure: function(data){
      console.error(data);
    }
  });
}

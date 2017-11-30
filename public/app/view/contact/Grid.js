let sm = Ext.create('Ext.selection.CheckboxModel');
Ext.define('Alegra.view.contact.Grid', {
  extend: 'Ext.grid.Panel',
  alias: 'widget.contactGrid',
  requires: ['Ext.toolbar.Paging'],
  title: 'Contactos',
  selModel: sm,
  store: 'Contacts',
  stripeRows: true,
  columnLines: true,
  id: 'contactGrid',
  columns: [{
    header: 'Nombre',
    width: 100,
    flex: 1,
    dataIndex: 'name',
    align: 'center',
    menuDisabled: true,
  }, {
    header: 'Identificación',
    width: 100,
    flex: 1,
    dataIndex: 'identification',
    align: 'center',
    menuDisabled: true,
  }, {
    header: 'Teléfono',
    width: 100,
    flex: 1,
    dataIndex: 'phonePrimary',
    align: 'center',
    menuDisabled: true,
  }, {
    header: 'Observaciones',
    width: 100,
    flex: 1,
    dataIndex: 'observations',
    align: 'center',
    menuDisabled: true,
  }, {
    xtype: 'actioncolumn',
    width: 100,
    text: 'Acciones',
    align: 'center',
    flex: 1,
    menuDisabled: true,
    items: [
      {
        icon   : 'images/007.png',
        tooltip: 'Ver Contacto',
        handler: function(grid, rowIndex, colIndex, item, e, record, row) {
          var rec = grid.getStore().getAt(rowIndex);
          let formShow = Ext.create('Alegra.view.contact.Show').show();
          formShow.down('form').loadRecord(rec);
        },
      }, {
        icon   : 'images/022.png',
        tooltip: 'Editar',
        handler: function(grid, rowIndex, colIndex) {
          var rec = grid.getStore().getAt(rowIndex);
          let formEdit = Ext.create('Alegra.view.contact.Form').show();
          if (rec.stores != null) {
            formEdit.down('form').loadRecord(rec);
          }
        },
      }, {
        icon   : 'images/020.png',
        tooltip: 'Eliminar',
        handler: function(grid, rowIndex, colIndex) {
          let rec = grid.getStore().getAt(rowIndex);
          let store = grid.getStore();
      		Ext.Msg.show({
      			title: 'Eliminar cliente',
      			msg: '¿Estás seguro de que deseas eliminar el cliente? Esta operación no se puede deshacer',
      			buttons: Ext.Msg.YESNOCANCEL,
      			icon: Ext.MessageBox.QUESTION,
    				scope: this,
      			width: 600,
      			fn: function(btn) {
      				if (btn == 'yes') {
                let myMask = new Ext.LoadMask(Ext.getBody(), { msg:"Eliminando..." });
    						myMask.show();
      					store.remove(rec);
      					store.sync({
      						success: function (batch, action) {
      							myMask.hide();
      							store.load();
      							let reader = batch.proxy.getReader();
      							Ext.Msg.alert('Success', reader.jsonData.message );
      						},
      						failure: function (batch, action) {
      							myMask.hide();
      							let reader = batch.proxy.getReader();
      							Ext.Msg.alert('Failed', reader.jsonData ? reader.jsonData.message : 'No response');
      						},
    							scope: this,
      					});
      				}
      			}
      		});
        },
      },
    ],
  }],
  initComponent: function() {
    this.dockedItems = [{
      xtype: 'toolbar',
      dock:'top',
      items: [
        {
        text: 'Agregar',
        action: 'add',
      }, ],
    }, {
      xtype: 'pagingtoolbar',
      dock: 'bottom',
      store: 'Contacts',
      displayInfo: true,
      displayMsg: 'Contactos {0} - {1} de {2}',
      emptyMsg: "Sin contactos disponible.",
    }];
    this.callParent(arguments);
  }
});
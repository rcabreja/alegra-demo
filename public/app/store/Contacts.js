Ext.define('Alegra.store.Contacts', {
  extend: 'Ext.data.Store',
  model: 'Alegra.model.Contact',
  autoLoad: true,
  leadingBufferZone: 10,
	pageSize: 20,
  autoLoad: { start: 0, limit: 20 },
  proxy: {
    type: 'ajax',
    api: {
      create: 'api/create',
      read: 'api/index',
      update: 'api/update',
      destroy: 'api/delete',
    },
    actionMethods: {
      create: 'POST',
      read: 'GET',
      update: 'POST',
      destroy: 'POST',
    },
    reader: {
      type: 'json',
      root: 'data',
      successProperty: 'success',
    },
    writer: {
      type: 'json',
      writeAllFields: true,
      encode: true,
      root: 'data',
    },
  },
});

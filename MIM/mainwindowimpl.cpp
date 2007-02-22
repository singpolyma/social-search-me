#include "mainwindowimpl.h"

AddressWindowImpl* MainWindowImpl::viewAddressesWindow(int record, bool locked) {
  AddressWindowImpl *addressWindow = new AddressWindowImpl(record,locked,addressTable,currencyTable);
  addressWindow->show();
  return addressWindow;
}//end viewAddressesWindow(int,bool)

AddressWindowImpl* MainWindowImpl::viewAddressesWindow() {
   return viewAddressesWindow(0,true);
}//end viewAddressesWindow

void MainWindowImpl::addAddresses() {
   AddressWindowImpl *addressWindow = viewAddressesWindow(0,false);
   addressWindow->newRecord();
}//end addAddresses

void MainWindowImpl::selectBoxActivated(int record) {
   if(record < 1) {//if invalid (ie, they picked the '- Contacts -' entry)
      return;
   }//end if record < 0
   viewAddressesWindow(record - 1,true);
}//end viewAddressesWindow(int)

void MainWindowImpl::closeEvent(QCloseEvent *event) {
   qApp->quit();
}//end closeEvent

void MainWindowImpl::f11() {
   QStringList tables = db.tables();
   QSqlTableModel *tableA[tables.size()];
   QTableView *views[tables.size()];
   for(int i = 0; i < tables.size(); i++) {
      tableA[i] = new QSqlTableModel();
      tableA[i]->setTable(tables.at(i)	);
      tableA[i]->select();
      views[i] = new QTableView;
      views[i]->setModel(tableA[i]);
      views[i]->show();
      views[i]->setWindowTitle(tables.at(i));
   }//end for i < tables.size()
}//end f11

void MainWindowImpl::showMailingLabels() {
  MailingLabelsImpl *widget = new MailingLabelsImpl(addressTable);
  widget->show();	
}//end printTest

void MainWindowImpl::about() {
   QMessageBox::about(this, "About MIM", "MIM BETA 2.0");
}//end about

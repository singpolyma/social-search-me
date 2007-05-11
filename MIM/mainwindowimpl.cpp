/*

LICENSE


This program is free software; you can redistribute it 
and/or modify it under the terms of the GNU General Public 
License (GPL) as published by the Free Software Foundation; 
either version 2 of the License, or (at your option) any 
later version.

This program is distributed in the hope that it will be 
useful, but WITHOUT ANY WARRANTY; without even the 
implied warranty of MERCHANTABILITY or FITNESS FOR A 
PARTICULAR PURPOSE.  See the GNU General Public License 
for more details.

To read the license please visit
http://www.gnu.org/copyleft/gpl.html

*/

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

void MainWindowImpl::enterEvent(QEvent *event) {
//   qDebug() << "MainWindowImpl::enterEvent"; //do not display because it plays havoc with --debug
   addressTable->select();
   nameSelectBox->clear();
   QSqlRecord loopRecord;
   nameSelectBox->addItem("- Contacts -");
   for(int i = 0; i < addressTable->rowCount(); i++) {//loop through Addresses table
      loopRecord = addressTable->record(i);
      if(loopRecord.value("isOrganization").toString() == "1")
         nameSelectBox->addItem(loopRecord.value("organization").toString());
      else
         nameSelectBox->addItem(loopRecord.value("lastName").toString() + ", " + loopRecord.value("firstName").toString());
   }//end for i < addressTable->rowCount()
}//end printTest

void MainWindowImpl::about() {
   QMessageBox::about(this, tr("About MIM"), tr("<b>Work in progress.</b><br>Moving from MSAccess to Qt.<br>Versioning to be decided.<br>Dev stamp <b>1177706512</b><br><br><a href=\"http://mim.singpolyma.net/\">Project Website</a><br><a href=\"mailto:mim@singpolyma.net\">Email</a><br><br>Some icons from the <a href=\"http://famfamfam.com/lab/icons/silk/\">Silk icon set</a>"));
}//end about

void MainWindowImpl::import() {
   qDebug("MainWindowImpl::import");
   QSqlDatabase::registerSqlDriver( "SXWPLAIN", new QSqlDriverCreator<SxWPlainDriver> );
//   QMessageBox::information(this, tr("Import..."), tr("The importer is a work in progress.  This implementation only works on CSV files."));
   QString documentsPath = QDir::homePath();
   if(QDir(QDir::homePath() + tr("Documents") + QDir::separator()).exists()) documentsPath = QDir::homePath() + tr("Documents") + QDir::separator();
   if(QDir(QDir::homePath() + tr("My Documents") + QDir::separator()).exists()) documentsPath = QDir::homePath() + tr("My Documents") + QDir::separator();
   QString fileName = QFileDialog::getOpenFileName(this, tr("Open File"), documentsPath, tr("CSV Files (*.csv)"));
   
   QSqlDatabase db = QSqlDatabase::addDatabase("SXWPLAIN","import");
   db.setDatabaseName(fileName);
   if (!db.open()) {
       qFatal("SQL Driver not found");
       return;
   }//end if ! db.open
   QSqlQuery query(db);
   if (!query.exec("select")) {
      qFatal("Unable to perform Query");
      return;
   }//end if ! query.exec
//   qDebug() << "Query size:" << query.size(); //does not work, always -1
   query.next();
   (new SqlImportWindow(((SxWResult*)(query.result()))->record(),addressTable->record(0)))->show();
//   QSqlRecord loopRecord;
//   while (query.next()) {
//      loopRecord = ((SxWResult*)(query.result()))->record();//we should be able to use query.record()
//   }//end while query.next
   db.close();
}//end import

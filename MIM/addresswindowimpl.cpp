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

#include "addresswindowimpl.h"

void AddressWindowImpl::refresh(bool newRecord) {
   DatabaseWindow::refresh(newRecord);
   if(!newRecord) {
      setWindowTitle(getTable()->tableName() + " - " + (currentRecord.value("lastName").toString() == "" ? "" : currentRecord.value("lastName").toString() + ", ") + currentRecord.value("firstName").toString());
   }//end if ! newRecord
   refreshCategories();
   calculateSupport();
   businessToggle(isBusinessBox->checkState());
}//end refresh(bool newRecord)

void AddressWindowImpl::refresh() {
   AddressWindowImpl::refresh(false);
}//end refresh(bool newRecord)

bool AddressWindowImpl::lock(bool state, QList<QObject *> *get) {
   bool rtrn = DatabaseWindow::lock(state, get);
   if(state) {
      isBusinessBox->setVisible(false);
      addCategoryButton->setVisible(false);
      deleteCategoryButton->setVisible(false);
      newCategoryCombo->setVisible(false);
   } else {
      isBusinessBox->setVisible(true);
      addCategoryButton->setVisible(true);
      deleteCategoryButton->setVisible(true);
      newCategoryCombo->setVisible(true);
   }//end if-else state
   return rtrn;
}//end refresh(bool newRecord)

void AddressWindowImpl::calculateSupport() {
   monthlyCalcBox->setText((new QString())->setNum(((supportBox->text().toDouble() * periodBox->text().toDouble()) / 12) * currencyBox->itemData(currencyBox->currentIndex()).toDouble(),'f',2));
}//end calculateSupport

void AddressWindowImpl::calculateSupport(int i) {
   calculateSupport();
}//end calculateSupport int i

void AddressWindowImpl::refreshCategories() {
   qDebug("AddressWindowImpl::refreshCategories");
   QSqlQueryModel *query = new QSqlQueryModel();
   query->setQuery("SELECT category FROM Addresses2Categories WHERE address_id="+currentRecord.value("id").toString());
   if(query->lastError().isValid()) qDebug() << query->lastError();
   categoryList->setModel(query);
   
   query = new QSqlQueryModel();
   query->setQuery("SELECT DISTINCT category FROM Addresses2Categories");
   if(query->lastError().isValid()) qDebug() << query->lastError();
   newCategoryCombo->setModel(query);
}//end refreshCategories
   
void AddressWindowImpl::businessToggle(int state) {
   if(state == Qt::Checked) {
      lastNameBox->setText("");
      lastNameBox->setVisible(false);
      lastNameLabel->setVisible(false);
      titleBox->setVisible(false);
      titleBox->setText("");
      firstNameLabel->setText("Business Name");
   } else {
      lastNameBox->setVisible(true);
      lastNameLabel->setVisible(true);
      titleBox->setVisible(true);
      firstNameLabel->setText("First Name");
   }//end if-else state == Qt::Checked
}//end calculateSupport int i

void AddressWindowImpl::addCategory() {
   //Perhaps this should check if the category is already on the item?
   qDebug("AddressWindowImpl::addCategory");
   QSqlQuery query;
   query.exec("INSERT INTO Addresses2Categories values(null, '" + newCategoryCombo->currentText().trimmed().toLower() + "', " + currentRecord.value("id").toString() + ")");
   if(query.lastError().isValid()) qDebug() << query.lastError();
   refreshCategories();
}//end addCategory

void AddressWindowImpl::deleteCategory() {
   qDebug("AddressWindowImpl::deleteCategory");
   QSqlQuery query;
   query.exec("DELETE FROM Addresses2Categories WHERE category='" + categoryList->model()->data(categoryList->currentIndex()).toString() + "' AND address_id=" + currentRecord.value("id").toString());
   if(query.lastError().isValid()) qDebug() << query.lastError();
   refreshCategories();
}//end deleteCategory

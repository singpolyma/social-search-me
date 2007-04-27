#include "addresswindowimpl.h"

void AddressWindowImpl::refresh(bool newRecord) {
   DatabaseWindow::refresh(newRecord);
   if(!newRecord) {
      setWindowTitle(getTable()->tableName() + " - " + (currentRecord.value("lastName").toString() == "" ? "" : currentRecord.value("lastName").toString() + ", ") + currentRecord.value("firstName").toString());
   }//end if ! newRecord
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
   } else {
      isBusinessBox->setVisible(true);
   }//end if-else state
   return rtrn;
}//end refresh(bool newRecord)

void AddressWindowImpl::calculateSupport() {
   monthlyCalcBox->setText((new QString())->setNum(((supportBox->text().toDouble() * periodBox->text().toDouble()) / 12) * currencyBox->itemData(currencyBox->currentIndex()).toDouble(),'f',2));
}//end calculateSupport

void AddressWindowImpl::calculateSupport(int i) {
   calculateSupport();
}//end calculateSupport int i
   
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

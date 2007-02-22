#include "addresswindowimpl.h"

void AddressWindowImpl::refresh(bool newRecord) {
   DatabaseWindow::refresh(newRecord);
   if(!newRecord) {
      setWindowTitle(getTable()->tableName() + " - " + currentRecord.value("lastName").toString() + ", " + currentRecord.value("firstName").toString());
   }//end if ! newRecord
   calculateSupport();
}//end refresh(bool newRecord)

void AddressWindowImpl::refresh() {
   AddressWindowImpl::refresh(false);
}//end refresh(bool newRecord)

void AddressWindowImpl::calculateSupport() {
   monthlyCalcBox->setText((new QString())->setNum(((supportBox->text().toDouble() * periodBox->text().toDouble()) / 12) * currencyBox->itemData(currencyBox->currentIndex()).toDouble(),'f',2));
}//end calculateSupport

void AddressWindowImpl::calculateSupport(int i) {
   calculateSupport();
}//end calculateSupport int i

#include "mailinglabelsimpl.h"

void MailingLabelsImpl::doPaint(QPainter *painter, QPrinter *printer,int x, int y) {
   if(printer == 0) {
      painter->drawRect(x + 4, y + 32, 80*10, 250*3);
   }//end if printer == 0

   int totalCount = 0;//total records printed
   QSqlRecord loopRecord;

   while(totalCount < addressTable->rowCount()) {

      for(int i = 0; i < 10 && totalCount < addressTable->rowCount(); i++) {//first column
         loopRecord = addressTable->record(totalCount);
         painter->drawText(x + 4, y + 32 + (97 * i), 250, 94, Qt::AlignVCenter | Qt::AlignHCenter | Qt::TextWordWrap, loopRecord.value("lastName").toString() + ", " + loopRecord.value("firstName").toString() + "\n" + loopRecord.value("streetAddress").toString() + "\n" + loopRecord.value("city").toString() + ", " + loopRecord.value("province").toString() + "\n" + loopRecord.value("postalCode").toString() + "\n" + loopRecord.value("country").toString());
         totalCount++;
      }//end for i < 10 (first column)
 
      if(!(totalCount < addressTable->rowCount())) {break;}//check main loop condition between sub-loops

      for(int i = 0; i < 10 && totalCount < addressTable->rowCount(); i++) {//second column
         loopRecord = addressTable->record(totalCount);
         painter->drawText(x + (4 + 263), y + 32 + (97 * i), 250, 94, Qt::AlignVCenter | Qt::AlignHCenter | Qt::TextWordWrap, loopRecord.value("lastName").toString() + ", " + loopRecord.value("firstName").toString() + "\n" + loopRecord.value("streetAddress").toString() + "\n" + loopRecord.value("city").toString() + ", " + loopRecord.value("province").toString() + "\n" + loopRecord.value("postalCode").toString() + "\n" + loopRecord.value("country").toString());
         totalCount++;
      }//end for i < 10 (second column)

      if(!(totalCount < addressTable->rowCount())) {break;}//check main loop condition between sub-loops

      for(int i = 0; i < 10 && totalCount < addressTable->rowCount(); i++) {//second column
         loopRecord = addressTable->record(totalCount);
         painter->drawText(x + (4 + (263*2)), y + 32 + (97 * i), 250, 94, Qt::AlignVCenter | Qt::AlignHCenter | Qt::TextWordWrap, loopRecord.value("lastName").toString() + ", " + loopRecord.value("firstName").toString() + "\n" + loopRecord.value("streetAddress").toString() + "\n" + loopRecord.value("city").toString() + ", " + loopRecord.value("province").toString() + "\n" + loopRecord.value("postalCode").toString() + "\n" + loopRecord.value("country").toString());
         totalCount++;
      }//end for i < 10 (second column)

      if(!(totalCount < addressTable->rowCount())) {break;}//check main loop condition between sub-loops

      if(printer != 0) {
         printer->newPage();
      } else {
         break;
      }//end if-else print

   }//end while totalCount < addressTable->rowCount()

   painter->end();
}//end printTest

void MailingLabelsImpl::doPrint() {
   QPrinter *printer = new QPrinter();
   QPrintDialog dialog(printer);
   if(dialog.exec() != QDialog::Accepted)
      return;
   QPainter painter;
   painter.begin(printer);
   doPaint(&painter, printer, 0, 0);
}//end doPrint

void MailingLabelsImpl::doMailMerge() {
   QMessageBox::information(this, "Not Implemented", "This feature has not been implemented yet.");
}//end doMailMerge

void MailingLabelsImpl::paintEvent(QPaintEvent *event) {
   gridLayout->resize(width()-10,gridLayout->height());
   QWidget::paintEvent(event);
   QPainter painter;
   painter.begin(this);
   doPaint(&painter, 0, (width()/2)-((270*3)/2), 20);
}//end paintEvent

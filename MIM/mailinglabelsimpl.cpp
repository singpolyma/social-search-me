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

#include "mailinglabelsimpl.h"

void MailingLabelsImpl::doPaint(QPainter *painter, QPrinter *printer,int x, int y) {
   if(printer == 0) {
      painter->drawRect(x + 4, y + 32, 80*10, 250*3);
   }//end if printer == 0

   int totalCount = 0;//total records printed
   QSqlRecord loopRecord;
   QString nameThing;
   QString tmpstr = "";
   QSqlQueryModel *query;
   
   if(categorySelect->currentText() == "All Categories") {//if selecting all categories
      query = addressTable;
   } else {//otherwise, SELECT
      query = new QSqlQueryModel();
      query->setQuery("SELECT address_id FROM Addresses2Categories WHERE category='" + categorySelect->currentText() + "'");
      if(query->lastError().isValid()) qDebug() << query->lastError();
      for(int i = 0; i < query->rowCount(); i++) {
	      loopRecord = query->record(i);
         tmpstr += " OR id=" + loopRecord.value("address_id").toString();
      }//end for i < currencyTable->rowCount()
      query->setQuery("SELECT * FROM Addresses WHERE " + tmpstr.remove(0,4));
      if(query->lastError().isValid()) qDebug() << query->lastError();
   }//end if-else All Categories

   while(totalCount < query->rowCount()) {

      for(int i = 0; i < 10 && totalCount < query->rowCount(); i++) {//first column
         loopRecord = query->record(totalCount);
         nameThing = loopRecord.value("lastName").toString() + ", " + (loopRecord.value("title").toString() == "" ? "" : loopRecord.value("title").toString() + " ") + loopRecord.value("firstName").toString();
         if(loopRecord.value("isOrganization").toString() == "1") nameThing = loopRecord.value("organization").toString() + "\n" + nameThing;
         painter->drawText(x + 4, y + 32 + (97 * i), 250, 94, Qt::AlignVCenter | Qt::AlignHCenter | Qt::TextWordWrap, nameThing + "\n" + loopRecord.value("streetAddress").toString() + "\n" + loopRecord.value("city").toString() + ", " + loopRecord.value("province").toString() + "\n" + loopRecord.value("postalCode").toString() + "\n" + loopRecord.value("country").toString());
         totalCount++;
      }//end for i < 10 (first column)
 
      if(!(totalCount < query->rowCount())) {break;}//check main loop condition between sub-loops

      for(int i = 0; i < 10 && totalCount < query->rowCount(); i++) {//second column
         loopRecord = query->record(totalCount);
         nameThing = loopRecord.value("lastName").toString() + ", " + (loopRecord.value("title").toString() == "" ? "" : loopRecord.value("title").toString() + " ") + loopRecord.value("firstName").toString();
         painter->drawText(x + (4 + 263), y + 32 + (97 * i), 250, 94, Qt::AlignVCenter | Qt::AlignHCenter | Qt::TextWordWrap, nameThing + "\n" + loopRecord.value("streetAddress").toString() + "\n" + loopRecord.value("city").toString() + ", " + loopRecord.value("province").toString() + "\n" + loopRecord.value("postalCode").toString() + "\n" + loopRecord.value("country").toString());
         totalCount++;
      }//end for i < 10 (second column)

      if(!(totalCount < query->rowCount())) {break;}//check main loop condition between sub-loops

      for(int i = 0; i < 10 && totalCount < query->rowCount(); i++) {//second column
         loopRecord = query->record(totalCount);
         nameThing = loopRecord.value("lastName").toString() + ", " + (loopRecord.value("title").toString() == "" ? "" : loopRecord.value("title").toString() + " ") + loopRecord.value("firstName").toString();
         painter->drawText(x + (4 + (263*2)), y + 32 + (97 * i), 250, 94, Qt::AlignVCenter | Qt::AlignHCenter | Qt::TextWordWrap, nameThing + "\n" + loopRecord.value("streetAddress").toString() + "\n" + loopRecord.value("city").toString() + ", " + loopRecord.value("province").toString() + "\n" + loopRecord.value("postalCode").toString() + "\n" + loopRecord.value("country").toString());
         totalCount++;
      }//end for i < 10 (second column)

      if(!(totalCount < query->rowCount())) {break;}//check main loop condition between sub-loops

      if(printer != 0) {
         printer->newPage();
      } else {
         break;
      }//end if-else print

   }//end while totalCount < query->rowCount()

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

void MailingLabelsImpl::doRefresh(int i) {
   repaint(0,0,-1,-1);
}//end doRefresh

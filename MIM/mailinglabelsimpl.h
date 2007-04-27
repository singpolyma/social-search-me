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

#ifndef MAILINGLABELS_H
#define MAILINGLABELS_H

#include <QWidget>
#include <QPrinter>
#include <QPrintDialog>
#include <QPainter>
#include <QPaintEvent>
#include <QtSql>
#include <QDesktopWidget>
#include <QMessageBox>
#include <QtDebug>

#include "ui_mailinglabels.h"

class MailingLabelsImpl : public QWidget, Ui::MailingLabels
{
  Q_OBJECT

public:
   MailingLabelsImpl(QSqlTableModel *table) : QWidget() {

      setupUi(this);

      addressTable = table;
      resize(270*3 + 30,105*3 + 20);
      move(qApp->desktop()->screenGeometry(this).center().x() - (width()/2),qApp->desktop()->screenGeometry(this).center().y() - (height()/2));
      
      QSqlQueryModel *query = new QSqlQueryModel();
      QSqlRecord loopRecord;
      query->setQuery("SELECT DISTINCT category FROM Addresses2Categories");
      if(query->lastError().isValid()) qDebug() << query->lastError();
      categorySelect->addItem("All Categories");
      for(int i = 0; i < query->rowCount(); i++) {
	      loopRecord = query->record(i);
         categorySelect->addItem(loopRecord.value("category").toString());
      }//end for i < currencyTable->rowCount()

      connect(printButton, SIGNAL(clicked()), this, SLOT(doPrint()));
      connect(mailMergeButton, SIGNAL(clicked()), this, SLOT(doMailMerge()));
      connect(categorySelect, SIGNAL(currentIndexChanged(int)), this, SLOT(doRefresh(int)));

   }//end constructor

public slots:
   virtual void doPaint(QPainter *painter, QPrinter *printer, int x, int y);
   virtual void doPrint();
   virtual void doMailMerge();
   virtual void doRefresh(int i);

protected:
   virtual void paintEvent(QPaintEvent *event);
   QSqlTableModel *addressTable;

};

#endif // MAILINGLABELS_H

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

#ifndef ADDRESSWINDOW_H
#define ADDRESSWINDOW_H

#include <QVariant>
#include <QSize>
#include <QtDebug>
#include "ui_addresswindow.h"
#include "databasewindow.h"

class AddressWindowImpl : public DatabaseWindow, Ui::AddressWindow
{
  Q_OBJECT

public:
   AddressWindowImpl(int record, bool locked, QSqlTableModel *table, QSqlTableModel *currencyTable) : DatabaseWindow(record, locked, table, false) {

      //window style/position
      setupUi(this);
      _init();
      move(qApp->desktop()->screenGeometry(this).center().x() - (width()/2),qApp->desktop()->screenGeometry(this).center().y() - (height()/2));
	  
      //get currencies for currency dropdown
      QSqlRecord loopRecord;
      int totalCount = 0;//total records added
      while(totalCount < currencyTable->rowCount()) {
	     loopRecord = currencyTable->record(totalCount);
         currencyBox->addItem(loopRecord.value("symbol").toString(), QVariant(loopRecord.value("value").toString()));
		 totalCount++;
      }//end while totalCount < currencyTable->rowCount()

      toolBar->addAction(QIcon(":/icons/resultset_first.png"), "First",this,SLOT(firstRecord()));
      toolBar->addAction(QIcon(":/icons/resultset_previous.png"), "Previous",this,SLOT(previousRecord()));
      toolBar->addAction(QIcon(":/icons/resultset_next.png"), "Next",this,SLOT(nextRecord()));
      toolBar->addAction(QIcon(":/icons/resultset_last.png"), "Last",this,SLOT(lastRecord()));
      toolBar->addSeparator();
      toolBar->addAction(QIcon(":/icons/add.png"), "New",this,SLOT(newRecord()));
      toolBar->addAction(QIcon(":/icons/delete.png"), "Delete",this,SLOT(deleteRecord()));
      toolBar->addSeparator();
      toolBar->addAction(QIcon(":/icons/pencil.png"), "Unlock",this,SLOT(unlock()));
      toolBar->addAction(QIcon(":/icons/disk.png"), "Save",this,SLOT(saveRecord()));
      toolBar->setIconSize(QSize(16,16));
	  
      connect(supportBox, SIGNAL(editingFinished()), this, SLOT(calculateSupport()));
      connect(periodBox, SIGNAL(editingFinished()), this, SLOT(calculateSupport()));
      connect(currencyBox, SIGNAL(currentIndexChanged(int)), this, SLOT(calculateSupport(int)));
      connect(addCategoryButton, SIGNAL(clicked()), this, SLOT(addCategory()));
      connect(deleteCategoryButton, SIGNAL(clicked()), this, SLOT(deleteCategory()));

   }//end constructor

public slots:
   virtual void refresh(bool newRecord);
   virtual void refresh();
   virtual bool lock(bool state, QList<QObject *> *get);
   virtual void calculateSupport();
   virtual void calculateSupport(int i);
   virtual void addCategory();
   virtual void deleteCategory();
   
protected:
   virtual void refreshCategories();

};

#endif // ADDRESSWINDOW_H

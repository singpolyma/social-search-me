#ifndef ADDRESSWINDOW_H
#define ADDRESSWINDOW_H

#include <QVariant>
#include "ui_addresswindow.h"
#include "databasewindow.h"

class AddressWindowImpl : public DatabaseWindow, Ui::AddressWindow
{
  Q_OBJECT

public:
   AddressWindowImpl(int record, bool locked, QSqlTableModel *table, QSqlTableModel *currencyTable) : DatabaseWindow(record, locked, table, false) {

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

      toolBar->addAction("Previous",this,SLOT(previousRecord()));
      toolBar->addAction("Next",this,SLOT(nextRecord()));
      toolBar->addAction("New",this,SLOT(newRecord()));
      toolBar->addAction("Unlock",this,SLOT(unlock()));
	  
      connect(supportBox, SIGNAL(editingFinished()), this, SLOT(calculateSupport()));
      connect(periodBox, SIGNAL(editingFinished()), this, SLOT(calculateSupport()));
      connect(currencyBox, SIGNAL(currentIndexChanged(int)), this, SLOT(calculateSupport(int)));
      connect(isBusinessBox, SIGNAL(stateChanged(int)), this, SLOT(businessToggle(int)));

   }//end constructor

public slots:
   virtual void refresh(bool newRecord);
   virtual void refresh();
   virtual bool lock(bool state, QList<QObject *> *get);
   virtual void calculateSupport();
   virtual void calculateSupport(int i);
   virtual void businessToggle(int state);

};

#endif // ADDRESSWINDOW_H

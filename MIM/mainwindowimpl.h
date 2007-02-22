#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include <QApplication>
#include <QMainWindow>
#include <QtSql>
#include <QDesktopWidget>
#include <QStringList>
#include <QShortcut>
#include <QKeySequence>
#include <QTableView>
#include <QVariant>
#include <QMap>

#include "ui_mainwindow.h"
#include "addresswindowimpl.h"
#include "mailinglabelsimpl.h"

class MainWindowImpl : public QMainWindow, Ui::MainWindow
{
  Q_OBJECT

public:
   QSqlDatabase db;//database link

   MainWindowImpl() : QMainWindow() {

      setupUi(this);

      //window style/position
      setWindowFlags(Qt::CustomizeWindowHint);
      setWindowFlags(Qt::WindowTitleHint);
      setWindowFlags(Qt::WindowSystemMenuHint);
      setWindowFlags(Qt::WindowMinimizeButtonHint);
      move(qApp->desktop()->screenGeometry(this).center().x() - (width()/2),qApp->desktop()->screenGeometry(this).center().y() - height());

      db = QSqlDatabase::addDatabase("QSQLITE");//open database
      db.setDatabaseName(":memory:");
      if (!db.open()) {
          //SQL Driver not found
          qApp->exit(1);
      }//end if ! db.open

	  
	  /* DATABASE SETUP */
QSqlQuery query;
query.exec("CREATE TABLE Addresses (id INTEGER PRIMARY KEY, title VARCHAR(5), firstName VARCHAR(20), lastName VARCHAR(20), streetAddress VARCHAR(50), city VARCHAR(20), province VARCHAR(20), postalCode VARCHAR(10), country VARCHAR(20), spouseName VARCHAR(20), homePhone VARCHAR(11), workPhone VARCHAR(11), workExtension VARCHAR(3), fax VARCHAR(11), cellPhone VARCHAR(11), email VARCHAR(50), url VARCHAR(255), birthdate DATE, anniversary DATE, notes TEXT, support INTEGER, currency VARCHAR(3), period INTEGER)");
for(int i = 0; i < 32; i++)
query.exec("INSERT INTO Addresses values(null, 'Mr.', 'Danny " + (new QVariant(i))->toString() + "', 'Young', '380 Louisa St.', 'Wako', 'BC', 'H2H 6J7', 'Canada', '', '', '', '', '', '', 'dude@place.net', 'http://example.com/', '', '', '', 1, 'CDN', 12)");
query.exec("CREATE TABLE Currencies (id INTEGER PRIMARY KEY, symbol VARCHAR(3), value INTEGER)");
query.exec("INSERT INTO Currencies values(null, 'CDN', 1)");
query.exec("INSERT INTO Currencies values(null, 'USD', 1.3)");

      addressTable = new QSqlTableModel();
      addressTable->setTable("Addresses");
      addressTable->setEditStrategy(QSqlTableModel::OnManualSubmit);
      addressTable->select();
	  
	  currencyTable = new QSqlTableModel();
      currencyTable->setTable("Currencies");
	  currencyTable->setEditStrategy(QSqlTableModel::OnManualSubmit);
      currencyTable->select();

      QSqlRecord loopRecord;
      nameSelectBox->addItem("- Contacts -");
      for(int i = 0; i < addressTable->rowCount(); i++) {//loop through Addresses table
         loopRecord = addressTable->record(i);
         nameSelectBox->addItem(loopRecord.value("lastName").toString() + ", " + loopRecord.value("firstName").toString());
      }//end for i < addressTable->rowCount()

      connect(viewAddressesButton, SIGNAL(clicked()), this, SLOT(viewAddressesWindow()));
      connect(addAddressButton, SIGNAL(clicked()), this, SLOT(addAddresses()));
      connect(actionAbout_Qt, SIGNAL(activated()), qApp, SLOT(aboutQt()));
      connect(actionAbout_MIM, SIGNAL(activated()), this, SLOT(about()));
      connect(nameSelectBox, SIGNAL(currentIndexChanged(int)), this, SLOT(selectBoxActivated(int)));
      connect(mailingLabelsButton, SIGNAL(clicked()), this, SLOT(showMailingLabels()));
      new QShortcut(*new QKeySequence("F11"), this, SLOT(f11()), SLOT(f11()), Qt::ApplicationShortcut);

   }//end constructor

   virtual void closeEvent(QCloseEvent *event);

protected slots:
   virtual AddressWindowImpl* viewAddressesWindow();
   virtual AddressWindowImpl* viewAddressesWindow(int record,bool locked);
   virtual void addAddresses();
   virtual void selectBoxActivated(int record);
   virtual void f11();
   virtual void showMailingLabels();
   virtual void about();
   
protected:
   QSqlTableModel *addressTable;
   QSqlTableModel *currencyTable;

};

#endif // MAINWINDOW_H

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
#include <QErrorMessage>
#include <QFileDialog>

#include "ui_mainwindow.h"
#include "addresswindowimpl.h"
#include "mailinglabelsimpl.h"
#include "sxwplaindriver.h"
#include "sqlimportwindow.h"

class MainWindowImpl : public QMainWindow, Ui::MainWindow
{
  Q_OBJECT

public:
   QSqlDatabase db;//database link

   MainWindowImpl() : QMainWindow() {

     QStringList args = qApp->arguments();
     if(args.contains("--help") || args.contains("\?") || args.contains("/?") || args.contains("-h")) {
        printf("\nMissionary Information Manager - pre-release.\nQt version %s\n\nSupported arguments:\n   --help     Displays this information.\n   --debug    Displays more obvious debug data.\n\n",qVersion());
        exit(0);
     }//end if --help
      if(qApp->arguments().contains("--debug")) QErrorMessage::qtHandler();

      setupUi(this);

      //window style/position
      setWindowFlags(Qt::CustomizeWindowHint);
      setWindowFlags(Qt::WindowTitleHint);
      setWindowFlags(Qt::WindowSystemMenuHint);
      setWindowFlags(Qt::WindowMinimizeButtonHint);
      move(qApp->desktop()->screenGeometry(this).center().x() - (width()/2),qApp->desktop()->screenGeometry(this).center().y() - height());

      db = QSqlDatabase::addDatabase("QSQLITE");//open database
      db.setDatabaseName(QDir::homePath() + "/.mimdb");
      if (!db.open()) {
          qFatal("SQL Driver not found");
          qApp->exit(1);
      }//end if ! db.open

	  
	  /* DATABASE SETUP */
		QSqlQuery query;
		query.exec("CREATE TABLE IF NOT EXISTS Addresses (id INTEGER PRIMARY KEY, isOrganization INTEGER, title VARCHAR(5), firstName VARCHAR(20), lastName VARCHAR(20), organization VARCHAR(20), streetAddress VARCHAR(50), city VARCHAR(20), province VARCHAR(20), postalCode VARCHAR(10), country VARCHAR(20), spouseName VARCHAR(20), homePhone VARCHAR(11), workPhone VARCHAR(11), workExtension VARCHAR(3), fax VARCHAR(11), cellPhone VARCHAR(11), email VARCHAR(50), url VARCHAR(255), birthdate DATE, anniversary DATE, notes TEXT, support INTEGER, currency VARCHAR(3), period INTEGER)");
/*for(int i = 0; i < 10; i++)
   query.exec("INSERT INTO Addresses values(null, 0, 'Mr.', 'Danny " + (new QVariant(i))->toString() + "', 'Young', '', '380 Louisa St.', 'Wako', 'BC', 'H2H 6J7', 'Canada', '', '', '', '', '', '', 'dude@place.net', 'http://example.com/', '', '', '', 1, 'CDN', 12)");
query.exec("INSERT INTO Addresses values(null, 1, 'Mr.', 'Danny', 'Young', 'Building', '380 Louisa St.', 'Wako', 'BC', 'H2H 6J7', 'Canada', '', '', '', '', '', '', 'dude@place.net', 'http://example.com/', '', '', '', 1, 'CDN', 12)");
query.exec("INSERT INTO Addresses values(null, 0, 'Mr.', 'Danny', 'Young', 'Building', '380 Louisa St.', 'Wako', 'BC', 'H2H 6J7', 'Canada', '', '', '', '', '', '', 'dude@place.net', 'http://example.com/', '', '', '', 1, 'CDN', 12)");
*/
	//currencies
	//WARNING - SYMBOL IS USED AS PRIMARY KEY IN PROGRAM (IE, SYMBOL IS STORED IN ADDRESSES TABLE)
	query.exec("CREATE TABLE IF NOT EXISTS Currencies (id INTEGER PRIMARY KEY, symbol CHAR(3), value INTEGER)");
	//tags
	query.exec("CREATE TABLE IF NOT EXISTS  Addresses2Categories (id INTEGER PRIMARY KEY, category VARCHAR(20), address_id INTEGER)");
//query.exec("INSERT INTO Addresses2Categories values(null, 'financial supporter', 1)");
//query.exec("INSERT INTO Addresses2Categories values(null, 'newsletter', 2)");

      addressTable = new QSqlTableModel();
      addressTable->setTable("Addresses");
      addressTable->setEditStrategy(QSqlTableModel::OnManualSubmit);
      addressTable->setSort(1,Qt::AscendingOrder);
      addressTable->setSort(5,Qt::AscendingOrder);
      addressTable->setSort(4,Qt::AscendingOrder);
      addressTable->setSort(3,Qt::AscendingOrder);
      addressTable->select();
		if(addressTable->rowCount() < 1) {
   		query.exec("INSERT INTO Addresses values(null, 0, '', 'MIM', 'Project', '', '', '', '', '', '', '', '', '', '', '', '', 'mim@singpolyma.net', 'http://mim.singpolyma.net/', '', '', '', 0, 'CDN', 0)");
      	addressTable->select();
		}//end if currencyTable->rowCount < 1
	  
      currencyTable = new QSqlTableModel();
      currencyTable->setTable("Currencies");
	   currencyTable->setEditStrategy(QSqlTableModel::OnManualSubmit);
      currencyTable->select();
		if(currencyTable->rowCount() < 1) {
			query.exec("INSERT INTO Currencies values(null, 'CDN', 1)");
			query.exec("INSERT INTO Currencies values(null, 'USD', 1.3)");
      	currencyTable->select();
		}//end if currencyTable->rowCount < 1
      
      enterEvent(new QEvent(QEvent::None));

      connect(viewAddressesButton, SIGNAL(clicked()), this, SLOT(viewAddressesWindow()));
      connect(addAddressButton, SIGNAL(clicked()), this, SLOT(addAddresses()));
      connect(actionAbout_Qt, SIGNAL(activated()), qApp, SLOT(aboutQt()));
      connect(actionAbout_MIM, SIGNAL(activated()), this, SLOT(about()));
      connect(actionImport, SIGNAL(activated()), this, SLOT(import()));
      connect(nameSelectBox, SIGNAL(currentIndexChanged(int)), this, SLOT(selectBoxActivated(int)));
      connect(mailingLabelsButton, SIGNAL(clicked()), this, SLOT(showMailingLabels()));
      new QShortcut(*new QKeySequence("F11"), this, SLOT(f11()), SLOT(f11()), Qt::ApplicationShortcut);

   }//end constructor

   virtual void closeEvent(QCloseEvent *event);
	virtual QSqlTableModel* getAddressTable() {return addressTable;};

protected slots:
   virtual AddressWindowImpl* viewAddressesWindow();
   virtual AddressWindowImpl* viewAddressesWindow(int record,bool locked);
   virtual void addAddresses();
   virtual void selectBoxActivated(int record);
   virtual void f11();
   virtual void showMailingLabels();
   virtual void about();
   virtual void import();
   
protected:
   QSqlTableModel *addressTable;
   QSqlTableModel *currencyTable;
   virtual void enterEvent(QEvent *event);

};
#endif // MAINWINDOW_H

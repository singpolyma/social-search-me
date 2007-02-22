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

      connect(printButton, SIGNAL(clicked()), this, SLOT(doPrint()));
      connect(mailMergeButton, SIGNAL(clicked()), this, SLOT(doMailMerge()));

   }//end constructor

public slots:
   virtual void doPaint(QPainter *painter, QPrinter *printer,int x, int y);
   virtual void doPrint();
   virtual void doMailMerge();

protected:
   virtual void paintEvent(QPaintEvent *event);
   QSqlTableModel *addressTable;

};

#endif // MAILINGLABELS_H

#include <QApplication>

#include "mainwindowimpl.h"

int main( int argc, char **argv )
{
  QApplication app( argc, argv );

  MainWindowImpl *mainwindow = new MainWindowImpl();
  mainwindow->show();

  return app.exec();
}

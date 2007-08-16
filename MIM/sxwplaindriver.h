/*
 * Copyright (C) 2007 Simon A. Wilper
 *
 * This file is part of the Qt SQL Plaintext Database Driver.
 * 
 * The Qt SQL Plaintext Database Driver is free software;
 * you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * The Qt SQL Plaintext Database Driver is distributed in the hope
 * that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

#ifndef SXWPLAINDRIVER_H
#define SXWPLAINDRIVER_H

#include <QtCore>
#include <QtSql>

class SxWResult : public QSqlResult {
    public:
        SxWResult(const QSqlDriver* driver);
        ~SxWResult();
        
        virtual QSqlRecord record();
        
    protected:
        virtual QStringList row(int row);
        virtual QVariant data(int index);
        virtual bool isNull(int index);
        virtual bool reset(const QString& query);
        virtual bool fetch(int index);
        virtual bool fetchFirst();
        virtual bool fetchNext();
        virtual bool fetchLast();
        virtual int size();
        virtual int numRowsAffected();

    private:
        QFile *f;
        QStringList contents;
        QStringList fieldNames;
};

class SxWPlainDriver : public QSqlDriver {
    public:
        SxWPlainDriver();
        ~SxWPlainDriver();

        virtual bool hasFeature( DriverFeature ) const;
        virtual bool open(
                const QString&,
                const QString&,
                const QString&,
                const QString&,
                int,
                const QString&
                );
        virtual void close();
        virtual QSqlResult* createResult() const;

        inline QFile* getFile() { return f; }

    private:
        QFile *f;
};

#endif // SXWPLAINDRIVER_H

# sic - simple irc client
#   (C)opyright MMVI Anselm R. Garbe

include config.mk

SRC = xlibtest.c
OBJ = ${SRC:.c=.o}

all: options xlibtest

options:
	@echo xlibtest build options:
	@echo "CFLAGS   = ${CFLAGS}"
	@echo "LDFLAGS  = ${LDFLAGS}"
	@echo "CC       = ${CC}"
	@echo "LD       = ${LD}"

.c.o:
	@echo CC $<
	@${CC} -c ${CFLAGS} $<

${OBJ}: config.mk

xlibtest: clean ${OBJ}
	@echo LD $@
	@${LD} -o $@ ${OBJ} ${LDFLAGS}
	@strip $@

clean:
	@echo cleaning
	@rm -f xlibtest ${OBJ} xlibtest-${VERSION}.tar.gz

dist: clean
	@echo creating dist tarball
	@mkdir -p xlibtest-${VERSION}
	@cp -R Makefile README config.mk xlibtest.1 ${SRC} xlibtest-$VERSION}
	@tar -cf xlibtest-${VERSION}.tar xlibtest-${VERSION}
	@gzip xlibtest-${VERSION}.tar
	@rm -rf xlibtest-${VERSION}

install: all
	@echo installing executable file to ${DESTDIR}${PREFIX}/bin
	@mkdir -p ${DESTDIR}${PREFIX}/bin
	@cp -f xlibtest ${DESTDIR}${PREFIX}/bin
	@chmod 755 ${DESTDIR}${PREFIX}/bin/xlibtest
	@echo installing manual page to ${DESTDIR}${MANPREFIX}/man1
	@mkdir -p ${DESTDIR}${MANPREFIX}/man1
	@sed 's/VERSION/${VERSION}/g' < xlibtest.1 > ${DESTDIR}${MANPREFIX}/man1/xlibtest.1
	@chmod 644 ${DESTDIR}${MANPREFIX}/man1/xlibtest.1

uninstall:
	@echo removing executable file from ${DESTDIR}${PREFIX}/bin
	@rm -f ${DESTDIR}${PREFIX}/bin/xlibtest
	@echo removing manual page from ${DESTDIR}${MANPREFIX}/man1
	@rm -f ${DESTDIR}${MANPREFIX}/man1/xlibtest.1

.PHONY: all options clean dist install uninstall

/* (C)opyright MMV-MMVI Anselm R. Garbe <garbeam at gmail dot com>
 * (C)opyright MMV-MMVI Nico Golde <nico at ngolde dot de>
 * See LICENSE file for license details.
 */
#include <errno.h>
#include <netdb.h>
#include <netinet/in.h>
#include <stdarg.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <unistd.h>
#include <sys/socket.h>
#include <sys/time.h>

#define PINGTIMEOUT 300
#define MAXMSG 4096

static char *host = "irc.oftc.net";
static unsigned short port = 6667;
static char *password = NULL;
static char nick[32];
static int srv;
static char bufin[MAXMSG], bufout[MAXMSG];
static char channel[256];
static time_t trespond;

static void
eprint(const char *errstr, ...) {
	va_list ap;

	va_start(ap, errstr);
	vfprintf(stderr, errstr, ap);
	va_end(ap);
	exit(EXIT_FAILURE);
}

static int
getline(int fd, unsigned int len, char *buf) {
	unsigned int i = 0;
	char c;

	do {
		if(read(fd, &c, sizeof(char)) != sizeof(char))
			return -1;
		buf[i++] = c;
	}
	while(c != '\n' && i < len);
	buf[i - 1] = 0;
	return 0;
}

static void
pout(char *channel, char *msg) {
	static char timestr[18];
	time_t t = time(0);

	strftime(timestr, sizeof timestr, "%D %R", localtime(&t));
	fprintf(stdout, "%-12.12s: %s %s\n", channel, timestr, msg);
}

static void
privmsg(char *channel, char *msg) {
	if(channel[0] == 0)
		return;
	snprintf(bufout, sizeof bufout, "<%s> %s", nick, msg);
	pout(channel, bufout);
	snprintf(bufout, sizeof bufout, "PRIVMSG %s :%s\r\n", channel, msg);
	write(srv, bufout, strlen(bufout));
}

static void
parsein(char *msg) {
	char *p;

	if(msg[0] == 0)
		return;
	if(msg[0] != ':') {
		privmsg(channel, msg);
		return;
	}
	if(!strncmp(msg + 1, "j ", 2) && (msg[3] == '#'))
		snprintf(bufout, sizeof bufout, "JOIN %s\r\n", msg + 3);
	else if(!strncmp(msg + 1, "l ", 2))
		snprintf(bufout, sizeof bufout, "PART %s :sic - 250 LOC are too much!\r\n", msg + 3);
	else if(!strncmp(msg + 1, "m ", 2)) {
		if((p = strchr(msg + 3, ' ')))
			*(p++) = 0;
		privmsg(msg + 3, p);
		return;
	}
	else if(!strncmp(msg + 1, "s ", 2)) {
		strncpy(channel, msg + 3, sizeof channel);
		return;
	}
	else
		snprintf(bufout, sizeof bufout, "%s\r\n", msg + 1);
	write(srv, bufout, strlen(bufout));
}

static void
parsesrv(char *msg) {
	char *chan, *cmd, *p, *txt, *usr; 

	txt = NULL;
	usr = host;
	if(!msg || !(*msg))
		return;
	if(msg[0] != ':')
		cmd = msg;
	else {
		if(!(p = strchr(msg, ' ')))
			return;
		*p = 0;
		usr = msg + 1;
		cmd = ++p;
		if((p = strchr(usr, '!')))
			*p = 0;
	}
	for(p = cmd; *p; p++) /* remove CRLFs */
		if(*p == '\r' || *p == '\n')
			*p = 0;
	if((p = strchr(cmd, ':'))) {
		*p = 0;
		txt = ++p;
	}
	if(!strncmp("PONG", cmd, 4))
		return;
	if(!strncmp("PRIVMSG", cmd, 7) && txt) {
		if(!(p = strchr(cmd, ' ')))
			return;
		*p = 0;
		chan = ++p;
		for(; *p && *p != ' '; p++);
		*p = 0;
		snprintf(bufout, sizeof bufout, "<%s> %s", usr, txt);
		pout(chan, bufout);
	}
	else if(!strncmp("PING", cmd, 4) && txt) {
		snprintf(bufout, sizeof bufout, "PONG %s\r\n", txt);
		write(srv, bufout, strlen(bufout));
	}
	else {
		snprintf(bufout, sizeof bufout, ">< %s: %s", cmd, txt ? txt : "");
		pout(usr, bufout);
		if(!strncmp("NICK", cmd, 4) && !strncmp(usr, nick, sizeof nick) && txt)
			strncpy(nick, txt, sizeof nick);
	}
}

int
connectHost(char *host, char* nick, char *password, char *ping) {
	struct hostent *hp;
	static struct sockaddr_in addr;  /* initially filled with 0's */
	if((srv = socket(AF_INET, SOCK_STREAM, 0)) < 0) {
		printf("sic: cannot connect host '%s'\n", host);
		return 0;/* fail */
	}
	if(NULL == (hp = gethostbyname(host))) {
		printf("sic: cannot resolve hostname '%s'\n", host);
		return 0;/* fail */
	}
	addr.sin_family = AF_INET;
	addr.sin_port = htons(port);
	memcpy(&addr.sin_addr, hp->h_addr, hp->h_length);
	if(connect(srv, (struct sockaddr *) &addr, sizeof(struct sockaddr_in))) {
		close(srv);
		printf("sic: cannot connect host '%s'\n", host);
		return 0;/* fail */
	}
	/* login */
	if(password)
		snprintf(bufout, sizeof bufout,
				"PASS %s\r\nNICK %s\r\nUSER %s localhost %s :%s\r\n",
				password, nick, nick, host, nick);
	else
		snprintf(bufout, sizeof bufout, "NICK %s\r\nUSER %s localhost %s :%s\r\n",
				 nick, nick, host, nick);
	write(srv, bufout, strlen(bufout));
	snprintf(ping, sizeof ping, "PING %s\r\n", host);
	return 1;/* success */
}

int
main(int argc, char *argv[]) {
	int i;
	struct timeval tv;
	fd_set rd;
	char ping[256], *tmp;
	FILE *fp;

	strncpy(nick, getenv("USER"), sizeof nick);
	for(i = 1; i < argc; i++)
		if(!strncmp(argv[i], "-h", 3)) {
			if(++i < argc) host = argv[i];
		}
		else if(!strncmp(argv[i], "-p", 3)) {
			if(++i < argc) port = (unsigned short)atoi(argv[i]);
		}
		else if(!strncmp(argv[i], "-n", 3)) {
			if(++i < argc) strncpy(nick, argv[i], sizeof nick);
		}
		else if(!strncmp(argv[i], "-k", 3)) {
			if(++i < argc) password = argv[i];
		}
		else if(!strncmp(argv[i], "-v", 3))
			eprint("sic-"VERSION", (C)opyright MMVI Anselm R. Garbe\n");
		else
			eprint("usage: sic [-h host] [-p port] [-n nick] [-k keyword] [-v]\n");

	/* load server from ~/.sic, if present */
	fp=fopen(strcat(getenv("HOME"),"/.sic"),"r");
	if(argc < 2 && fp != 0) {
		char *tmp = malloc(255);
		fgets(tmp,255, fp);
		host = strtok(tmp,":");
		tmp = strtok(NULL,"\n");
		if(tmp != NULL && strcmp(tmp,"") != 0) port = (unsigned short)atoi(tmp);
		host = strtok(host,"\n");
	}

	/* init */
	if(!connectHost(host,nick,password,ping)) exit(EXIT_FAILURE);
	channel[0] = 0;
	setbuf(stdout, NULL); /* unbuffered stdout */
	
	/* load commands from ~/.sic, if present */
	if(argc < 2 && fp != 0) {
		tmp = malloc(255);
		while(fgets(tmp, 255, fp) != NULL) {
			printf("%s\n",tmp);
		  	parsein(tmp);
		}
	}
	fclose(fp);

	for(;;) { /* main loop */
		FD_ZERO(&rd);
		FD_SET(0, &rd);
		FD_SET(srv, &rd);
		tv.tv_sec = 120;
		tv.tv_usec = 0;
		i = select(srv + 1, &rd, 0, 0, &tv);
		if(i < 0) {
			if(errno == EINTR)
				continue;
			eprint("sic: error on select()");
		}
		else if(i == 0) {
			if(time(NULL) - trespond >= PINGTIMEOUT)
				eprint("sic shutting down: parse timeout");
			write(srv, ping, strlen(ping));
			continue;
		}
		if(FD_ISSET(srv, &rd)) {
			if(getline(srv, sizeof bufin, bufin) == -1)
				eprint("sic: remote host closed connection");
			parsesrv(bufin);
			trespond = time(NULL);
		}
		if(FD_ISSET(0, &rd)) {
			if(getline(0, sizeof bufin, bufin) == -1)
				eprint("sic: broken pipe");
			parsein(bufin);
		}
	}
	return 0;
}

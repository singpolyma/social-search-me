/* mnu.c by Stephen Paul Weber */
/* GPL */

#include "mnu.h"
#include "categories.h"

int file_select(struct direct *entry) {/* do a string comparison so that only .desktop files will be selected - probably should actually do this based on mime type */
	char *ptr = rindex(entry->d_name, '.');
	if(ptr != NULL && (strcmp(ptr, ".desktop") == 0))
		return TRUE;
	return FALSE;
}

void parse_desktop_file(char *path) {
	char line[100], *key, *val, *cat;
	FILE *fp = fopen(path,"r");
	MenuItem *this_item;
	DYNAMIC_STRUCT(this_item);
	while(fgets(line,100,fp)) {
		key = strtok(line,"=");
		val = strtok(NULL,"=\n");
		if(strcmp(key,"Name") == 0) {
			DYNAMIC_STRING_COPY(this_item->title,val);
		}
		if(strcmp(key,"Exec") == 0) {
			DYNAMIC_STRING_COPY(this_item->command,val);
		}
/*		if(strcmp(key,"Icon") == 0)*/
		if(strcmp(key,"Categories") == 0) {
			cat = strtok(val,";");
			while(cat != NULL) {
				add_to_cat(cat,this_item);
				cat = strtok(NULL,";");
			}/* end while */
		}/* end if Categories */
	}
	fclose(fp);
}

char** get_xdg_data_dirs() {
	char *tmp, *xdg_env = getenv("XDG_DATA_DIRS"), **xdg_data_dirs;
	if((xdg_data_dirs = calloc(MAX_XDG_DATA_DIRS,sizeof(char*))) == NULL) eprint("Memory allocation error.\n");
        if(!xdg_env || !xdg_env[0]) {/* if there in no envirenment variable, set defaults */
                xdg_data_dirs[0] = "/usr/local/share/applications/";
                xdg_data_dirs[1] = "/usr/share/applications/";
        } else {/* otherwise, parse environment variable */
                int i = 0;
                tmp = strtok(xdg_env,":");/* Start tokenization */
                while (tmp != NULL && i < MAX_XDG_DATA_DIRS) {/* while there is a token and we have not exceeded our directory scan maximum */
                        xdg_data_dirs[i] = strcat(tmp,"applications/");/* data_dir is this token + applications/ */
                        tmp = strtok(NULL,":");/* Get next token */
                        i++;
		}
	}
}

void read_xdg_menu() {
	int i, i2, fileCount;
	struct direct **files;
	char tmp[255], **xdg_data_dirs;
	xdg_data_dirs = get_xdg_data_dirs();
        for(i = 0; i < MAX_XDG_DATA_DIRS; i++) {
                if(xdg_data_dirs[i] == NULL || !xdg_data_dirs[i][0]) continue;
                fileCount = scandir(xdg_data_dirs[i], &files, (void*)file_select, alphasort);
                for(i2 = fileCount-1; i2 > 0; i2--) {
                        strcpy(tmp,xdg_data_dirs[i]);
                        strcat(tmp,files[i2]->d_name);
                        parse_desktop_file(tmp);
                }
        }
        free(files);
	free(xdg_data_dirs);
}

void draw_menu() {
	Menu *curr = current_menu_head;
	if(current_menu_item == NULL) {
		
	}
	while(curr != NULL) {
		if(curr == current_menu_item)
			printf("[*] %s\n",curr->item->title);
		else
			printf("[ ] %s\n",curr->item->title);
		curr = curr->nextNode;
	}
}

void menu_next() {
	if(current_menu_item == NULL) {//if we are on a category part of the menu
	} else {
		current_menu_item = current_menu_item->nextNode;
		if(current_menu_item == NULL) current_menu_item = current_menu_head;
	}
}

void menu_prev() {
	if(current_menu_item == NULL) {//if we are on a category part of the menu
	} else {
		current_menu_item = current_menu_item->previousNode;
		if(current_menu_item == NULL) current_menu_item = current_menu_head;
	}
}

void menu_go() {
	printf("\nLaunching program \"%s\"...\n",current_menu_item->item->title);
	if(fork() == 0) {
		if(fork() == 0) {
			execl("/bin/sh","/bin/sh","-c",current_menu_item->item->command,(char*)NULL);
		}
	}
	exit(0);
}

int main(int argc, char *argv[]) {
	int i;
	for(i = 1; i < argc; i++) /* handle command line arguments */
		if(!strncmp(argv[i], "-v", 3))
			eprint("mnu-"VERSION", (C)opyright 2007, Stephen Paul Weber\n");
		else
			eprint("usage: mnu\n");	
	
	read_xdg_menu();
	set_input_mode();
	current_menu_head = current_menu_item = Game;
	char in;
	while(in != 'q') {
		system("clear");
		draw_menu();
		read(STDIN_FILENO, &in, 1);
		if(in == 'j') menu_next();
		if(in == 'k') menu_prev();
		if(in == '\n') menu_go();
	}
	
	return 0;
}

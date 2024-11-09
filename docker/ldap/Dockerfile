FROM 389ds/dirsrv:3.0

ENV DS_DM_PASSWORD=asd
ENV DS_SUFFIX=dc=weeeopen,dc=net
ENV DS_ERRORLOG_LEVEL=16384

# Install git
RUN zypper install -y git

# Download schema files
RUN cd /tmp/ && git clone "https://github.com/WEEE-Open/schema.git"

# Add schema files to the server (ignore errors)
RUN cp /tmp/schema/* /etc/dirsrv/schema/ || :

# Remove schema files
RUN rm -rf /tmp/schema

# Create a new database, if error, it means it already exists
RUN dsconf localhost backend create --be-name weeeopen --suffix dc=weeeopen,dc=it || :

# Create groups, if error, it means they already exist
RUN dsconf localhost group create --gid chat || :
RUN dsconf localhost group create --gid software || :
RUN dsconf localhost group create --gid repairer || :
RUN dsconf localhost group create --gid soviet || :

# Create demo users, if error, it means they already exist
# new User({
# 	id: '3e8fe04c-5307-4190-8b42-285669f785b9',
# 	uid: 'eliot.alderson',
# 	name: 'Eliot',
# 	surname: 'Alderson',
# 	printableName: 'Eliot Alderson',
# 	accountLocked: false,
# 	email: 'eliot.alderson@ecorp.com',
# 	groups: ['chat','software','soviet'],
# 	hasKey: true,
# }),
# new User({
# 	id: 'f7639634-fd4e-4953-a15b-cddd091ae7d2',
# 	uid: 'darlene.alderson',
# 	name: 'Darlene',
# 	surname: 'Alderson',
# 	printableName: 'Darlene Alderson',
# 	accountLocked: false,
# 	email: 'darlene.alderson@fsociety.com',
# 	groups: ['chat','software'],
# 	hasKey: false,
# }),
# new User({
# 	id: '7691d893-4c17-4805-a8d9-8dfb484f7cfe',
# 	uid: 'tyrell.wellick',
# 	name: 'Tyrell',
# 	surname: 'Wellick',
# 	printableName: 'Tyrell Wellick',
# 	accountLocked: false,
# 	email: 'tyrell.wellick',
# 	groups: ['chat','repairer'],
# 	hasKey: false,
# }),
RUN dsconf localhost user create --uid eliot.alderson --cn "Eliot Alderson" --sn Alderson --mail "eliot.alderson@ecorp.com" --userpassword asd --memberof chat --memberof software --memberof soviet || :
RUN dsconf localhost user create --uid darlene.alderson --cn "Darlene Alderson" --sn Alderson --mail "darlene.alderson@fsociety.com" --userpassword asd --memberof chat --memberof software || :
RUN dsconf localhost user create --uid tyrell.wellick --cn "Tyrell Wellick" --sn Wellick --mail "tyrell.wellick@ecorp.com" --userpassword asd --memberof chat --memberof repairer || :

# Add users to the group
RUN dsconf localhost group add-member --uid eliot.alderson chat || :
RUN dsconf localhost group add-member --uid eliot.alderson software || :
RUN dsconf localhost group add-member --uid eliot.alderson soviet || :
RUN dsconf localhost group add-member --uid darlene.alderson chat || :
RUN dsconf localhost group add-member --uid darlene.alderson software || :
RUN dsconf localhost group add-member --uid tyrell.wellick chat || :
RUN dsconf localhost group add-member --uid tyrell.wellick repairer || :
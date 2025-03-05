FROM 389ds/dirsrv:3.0

ENV DS_DM_PASSWORD=asd
ENV DS_SUFFIX_NAME=dc=example,dc=test
ENV DS_ERRORLOG_LEVEL=16384

# Install git
RUN zypper install -y git

# Create a new database, if error, it means it already exists
#RUN dsconf localhost backend create --be-name weeeopen --suffix dc=example,dc=test || :

#RUN dsconf localhost backend create --be-name userRoot --suffix="dc=example,dc=test" --create-suffix

# Download schema files
RUN cd /tmp/ && git clone "https://github.com/WEEE-Open/schema.git"

# Add schema files to the server
RUN cp /tmp/schema/*.ldif /etc/dirsrv/schema/ && \
	cp /tmp/schema/aci/everything.ldif / && \
	cp /tmp/schema/aci/backend.ldif / && \
	sed '/#/d' /backend.ldif > /asd.ldif && \
	sed '/version/d' /everything.ldif >> /asd.ldif

#RUN dsconf localhost backend import userRoot /var/lib/dirsrv/slapd-localhost/ldif/everything.ldif

# Remove schema files
RUN rm -rf /tmp/schema

ADD docker/ldap/init.sh /init.sh

RUN chmod +x /init.sh

CMD /init.sh
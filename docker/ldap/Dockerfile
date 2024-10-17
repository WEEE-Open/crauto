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

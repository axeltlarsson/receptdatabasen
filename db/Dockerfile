FROM mysql:5

COPY schema.sql /docker-entrypoint-initdb.d
ENV TZ=Europe/Stockholm
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone


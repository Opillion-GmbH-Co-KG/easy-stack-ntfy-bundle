FROM binwiederhier/ntfy:latest

COPY --link --chown=root:root --chmod=644 resources/docker/dev/server.yml /etc/ntfy/server.yml

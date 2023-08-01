docker build -t keap-image .
docker run -dit --name keap-container -p $PORT:80 keap-image


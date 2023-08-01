docker build -t my-apache-image .
docker run -dit --name my-apache-container -p $PORT:80 my-apache-image


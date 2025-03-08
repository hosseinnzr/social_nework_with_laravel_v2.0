from pymilvus import connections, utility, Collection

# اتصال به Milvus
connections.connect(alias="default", host="localhost", port="19530")

# بررسی وجود مجموعه
if utility.has_collection("image_data"):
    print("Collection exists.")
else:
    print("Collection does not exist.")
    exit()

# دریافت اطلاعات ایندکس
collection = Collection("image_data")
index_info = collection.indexes
print("Index Info:", index_info)

# اتصال به Milvus
connections.connect(alias="default", host="localhost", port="19530")

# نام مجموعه Milvus
collection = Collection("image_data")

# نمایش اطلاعات ساختار مجموعه
print(collection.schema)


connections.connect(alias="default", host="localhost", port="19530")

# نام مجموعه
COLLECTION_NAME = "image_data"

# بارگذاری مجموعه
collection = Collection(COLLECTION_NAME)
collection.load()

results = collection.query(expr="", output_fields=["image_id", "image_name"])

for res in results:
    print(res)

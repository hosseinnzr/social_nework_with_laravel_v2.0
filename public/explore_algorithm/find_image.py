import sys
from pymilvus import connections, Collection

# اطلاعات اتصال به Milvus
MILVUS_HOST = "localhost"
MILVUS_PORT = "19530"
COLLECTION_NAME = "image_data"

def connect_to_milvus():
    """اتصال به پایگاه داده Milvus"""
    connections.connect(alias="default", host=MILVUS_HOST, port=MILVUS_PORT)

def get_images_from_milvus():
    """دریافت نام تمام تصاویر از دیتابیس و مرتب‌سازی آن‌ها"""
    collection = Collection(COLLECTION_NAME)
    print(collection.schema)
    collection.load()

    # دریافت همه تصاویر از پایگاه داده
    results = collection.query(expr="image_name != ''", output_fields=["image_name"])

    
    # تبدیل لیست دیکشنری‌ها به لیست نام تصاویر و مرتب کردن آن‌ها
    image_list = sorted([res["image_name"] for res in results])

    return image_list

def get_surrounding_images(image_list, target_image, num_neighbors=5):
    """دریافت ۵ عکس قبل و بعد از یک عکس مشخص"""
    if target_image not in image_list:
        return "Image not found in the list."

    index = image_list.index(target_image)

    # دریافت ۵ عکس قبل و بعد
    before_images = image_list[max(0, index - num_neighbors):index]
    after_images = image_list[index + 1: index + 1 + num_neighbors]

    return before_images, after_images

def main():
    if len(sys.argv) != 2:
        print("Usage: python find_image.py <image_name>")
        sys.exit(1)

    target_image = sys.argv[1]

    # اتصال به Milvus و دریافت لیست تصاویر
    connect_to_milvus()
    image_list = get_images_from_milvus()

    # دریافت تصاویر قبل و بعد
    before_images, after_images = get_surrounding_images(image_list, target_image)

    print(f"Images before {target_image}: {before_images}")
    print(f"Images after {target_image}: {after_images}")

if __name__ == "__main__":
    main()
